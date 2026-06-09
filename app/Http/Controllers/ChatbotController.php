<?php

namespace App\Http\Controllers;

use App\Mail\SupportTicketMail;
use App\Models\ChatbotLearnedKeyword;
use App\Models\ChatbotLog;
use App\Models\ChatbotUnhandledQuery;
use App\Services\ChatbotFlowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function handleChat(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'action' => 'required|string|in:chat,ticket,flow,navigate,back',
            'language' => 'required|string|in:ar,en',
        ]);

        try {
            $sessionId = $request->input('session_id');
            $action = $request->input('action');
            $lang = $request->input('language');
            $userId = auth()->check() ? auth()->id() : null;
            $userEmail = auth()->check() ? auth()->user()->email : null;

            // Handle Flow Navigation (new system)
            if ($action === 'flow') {
                return $this->handleFlowAction($request, $sessionId, $userId, $userEmail, $lang);
            }

            if ($action === 'navigate') {
                return $this->handleNavigateAction($request, $sessionId, $userId, $userEmail, $lang);
            }

            if ($action === 'back') {
                return $this->handleBackAction($request, $sessionId, $userId, $userEmail, $lang);
            }

            if ($action === 'ticket') {
                $request->validate([
                    'email' => 'required|email',
                    'message' => 'required|string',
                    'name' => 'nullable|string',
                    'category' => 'nullable|string',
                ]);

                return $this->submitTicketWithContext($request, $sessionId, $userId, $userEmail, $lang);
            }

            $message = $request->input('message');

            ChatbotLog::create([
                'user_id' => $userId,
                'user_email' => $userEmail,
                'session_id' => $sessionId,
                'sender' => 'user',
                'message' => $message,
                'language' => $lang,
                'log_type' => 'chat',
            ]);

            if ($this->isGreeting($message, $lang)) {
                $greetingReply = $lang === 'ar'
                    ? 'أهلاً بك! يمكنك اختيار أحد المسارات التالية للحصول على إجابة دقيقة وسريعة.'
                    : 'Welcome! Please choose one of the paths below for an accurate answer.';

                ChatbotLog::create([
                    'user_id' => $userId,
                    'user_email' => $userEmail,
                    'session_id' => $sessionId,
                    'sender' => 'bot',
                    'message' => $greetingReply,
                    'language' => $lang,
                    'log_type' => 'chat',
                ]);

                return response()->json([
                    'reply' => $greetingReply,
                    'flow' => 'chat',
                    'show_root_menu' => true,
                ]);
            }

            $learnedFlowMatch = $this->detectLearnedFlowFromMessage($message, $lang);
            $learnedFlowMatch = $this->detectLearnedFlowFromMessage($message, $lang);
            if ($learnedFlowMatch) {
                ChatbotLog::create([
                    'user_id' => $userId,
                    'user_email' => $userEmail,
                    'session_id' => $sessionId,
                    'sender' => 'bot',
                    'message' => 'Learned flow detection triggered',
                    'language' => $lang,
                    'log_type' => 'flow_detection',
                    'metadata' => json_encode($learnedFlowMatch),
                ]);

                if (($learnedFlowMatch['flow'] ?? '') === 'chitchat' || ($learnedFlowMatch['target_flow'] ?? '') === 'chitchat') {
                    $reply = $learnedFlowMatch['custom_response'] ?? ($lang === 'ar'
                        ? 'أهلاً بك! كيف يمكنني مساعدتك اليوم؟'
                        : 'Hello there! How can I help you today?');

                    ChatbotLog::create([
                        'user_id' => $userId,
                        'user_email' => $userEmail,
                        'session_id' => $sessionId,
                        'sender' => 'bot',
                        'message' => $reply,
                        'language' => $lang,
                        'log_type' => 'chat',
                    ]);

                    return response()->json([
                        'reply' => $reply,
                        'flow' => 'chat',
                        'show_root_menu' => true,
                    ]);
                }

                // تأمين جلب القيم سواء كانت التسمية flow أو target_flow لعدم حدوث خطأ null
                $extractedFlow = $learnedFlowMatch['flow'] ?? $learnedFlowMatch['target_flow'] ?? null;
                $extractedBranch = $learnedFlowMatch['branch'] ?? $learnedFlowMatch['target_branch'] ?? null;

                if (!$extractedFlow) {
                    // إذا لم يجد مسار، نخليه يكمل كأنه شات طبيعي وما يعطي 500
                    Log::warning('Learned flow detected but flow_key is missing', ['data' => $learnedFlowMatch]);
                } else {
                    // دمج البيانات بشكل آمن وتأكيد وجود الـ action المناسب للـ navigation
                    $request->merge([
                        'action' => 'navigate',
                        'flow_key' => $extractedFlow,
                        'branch_key' => $extractedBranch,
                    ]);

                    try {
                        return $this->handleNavigateAction($request, $sessionId, $userId, $userEmail, $lang);
                    } catch (\Throwable $navException) {
                        Log::error('Failed inside handleNavigateAction via LearnedFlow: ' . $navException->getMessage());
                        // كخطة بديلة (Fallback) حتى لا تظهر للمستخدم شاشة خطأ بيضاء
                        return response()->json([
                            'reply' => $lang === 'ar' ? 'جاري توجيهك...' : 'Redirecting...',
                            'flow' => 'chat',
                            'flow_key' => $extractedFlow,
                            'branch_key' => $extractedBranch
                        ]);
                    }
                }
            }

            // Try smart flow detection - match keywords to flows
            $flowMatch = $this->detectFlowFromMessage($message, $lang);
            if ($flowMatch) {
                // Log the detected flow
                ChatbotLog::create([
                    'user_id' => $userId,
                    'user_email' => $userEmail,
                    'session_id' => $sessionId,
                    'sender' => 'bot',
                    'message' => 'Smart flow detection triggered',
                    'language' => $lang,
                    'log_type' => 'flow_detection',
                    'metadata' => json_encode($flowMatch)
                ]);

                // Auto-navigate to the matched flow using navigate action
                $branchKey = $flowMatch['branch'] ?? null;
                $request->merge([
                    'flow_key' => $flowMatch['flow'],
                    'branch_key' => $branchKey
                ]);

                if (!empty($branchKey)) {
                    // Direct to branch
                    return $this->handleNavigateAction($request, $sessionId, $userId, $userEmail, $lang);
                }

                // Show branches for this flow
                return $this->handleNavigateAction($request, $sessionId, $userId, $userEmail, $lang);
            }

            if ($this->isSupportRequest($message, $lang)) {
                $supportReply = $lang === 'ar'
                    ? 'يبدو أنك تحتاج إلى دعم بشري. إذا أردت، يمكنك إرسال مشكلة تفصيلية عبر نموذج الدعم أدناه.'
                    : 'It looks like you need human support. If you wish, you can send a detailed issue using the support form below.';

                ChatbotLog::create([
                    'user_id' => $userId,
                    'user_email' => $userEmail,
                    'session_id' => $sessionId,
                    'sender' => 'bot',
                    'message' => $supportReply,
                    'language' => $lang,
                    'log_type' => 'chat',
                ]);

                return response()->json(['reply' => $supportReply, 'flow' => 'ticket_prompt']);
            }

            $faqAnswer = $this->findFaqAnswer($message, $lang);
            if ($faqAnswer !== null) {
                ChatbotLog::create([
                    'user_id' => $userId,
                    'user_email' => $userEmail,
                    'session_id' => $sessionId,
                    'sender' => 'bot',
                    'message' => $faqAnswer,
                    'language' => $lang,
                    'log_type' => 'chat',
                ]);

                return response()->json(['reply' => $faqAnswer, 'flow' => 'chat']);
            }

            $botResponse = $this->generateBotResponse($message, $lang);
            $replyText = $botResponse['reply'] ?? ($lang === 'ar'
                ? 'أعتذر، لم أتمكن من الإجابة الآن. يمكنك استخدام الدعم الفني إذا كنت تحتاج مساعدة إضافية.'
                : 'I’m sorry, I could not answer that right now. You can use support if you need additional help.');
            $fallback = $botResponse['fallback'] ?? false;

            if ($fallback) {
                $this->storeUnhandledQuery($message, $lang, [
                    'session_id' => $sessionId,
                    'user_id' => $userId,
                ]);
            }

            ChatbotLog::create([
                'user_id' => $userId,
                'user_email' => $userEmail,
                'session_id' => $sessionId,
                'sender' => 'bot',
                'message' => $replyText,
                'language' => $lang,
                'log_type' => 'chat',
            ]);

            $responsePayload = [
                'reply' => $replyText,
                'fallback' => $fallback,
                'flow' => $fallback ? 'ticket_prompt' : 'chat',
            ];

            return response()->json($responsePayload);
        } catch (\Exception $exception) {
            Log::error('Chatbot request failed: ' . $exception->getMessage(), ['exception' => $exception]);

            return response()->json([
                'reply' => $lang === 'ar'
                    ? 'عذراً، حدث خطأ في السيرفر. الرجاء المحاولة مرة أخرى.'
                    : 'Sorry, a server error occurred. Please try again.',
            ], 500);
        }
    }

    private function findFaqAnswer(string $message, string $language): ?string
    {
        $message = mb_strtolower($message);
        if ($language === 'ar') {
            $message = $this->normalizeArabicText($message);
        }

        $faqs = $this->faqRepository($language);

        foreach ($faqs as $faq) {
            foreach ($faq['keywords'] as $keyword) {
                $normalizedKeyword = mb_strtolower($keyword);
                if ($language === 'ar') {
                    $normalizedKeyword = $this->normalizeArabicText($normalizedKeyword);
                }

                if ($this->messageContainsKeyword($message, $normalizedKeyword, $language)) {
                    return $faq['answer'];
                }
            }
        }

        return null;
    }

    private function normalizeArabicText(string $text): string
    {
        $search = ['أ', 'إ', 'آ', 'ى', 'ئ', 'ؤ', 'ة', 'ـ'];
        $replace = ['ا', 'ا', 'ا', 'ي', 'ي', 'و', 'ه', ''];

        $text = str_replace(['أ', 'إ', 'آ', 'ى', 'ئ', 'ؤ', 'ة', 'ـ'], ['ا', 'ا', 'ا', 'ي', 'ي', 'و', 'ه', ''], $text);

        return str_replace($search, $replace, $text);
    }

    private function messageContainsKeyword(string $message, string $keyword, string $language): bool
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            return false;
        }

        if (mb_strlen($keyword) <= 2) {
            return preg_match('/(?<![\p{L}\p{N}])' . preg_quote($keyword, '/') . '(?![\p{L}\p{N}])/u', $message) === 1;
        }

        return Str::contains($message, $keyword);
    }

    private function isGreeting(string $message, string $language): bool
    {
        $text = mb_strtolower($message);
        if ($language === 'ar') {
            $text = $this->normalizeArabicText($text);
            $keywords = ['مرحبا', 'اهلا', 'هلا', 'اهلاً', 'سلام', 'صباح الخير', 'مساء الخير'];
        } else {
            $keywords = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening'];
        }

        foreach ($keywords as $keyword) {
            if (Str::contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function faqRepository(string $language): array
    {
        if ($language === 'ar') {
            return [
                [
                    'keywords' => ['كيف يحمي', 'يحمي السمعة', 'حماية السمعة', 'سمعة شركتي'],
                    'answer' => 'RevShieldra يحمي سمعة شركتك عبر توجيه العملاء الراضين إلى Google واحتجاز الشكاوى غير الراضين في نموذج داخلي سري، دون أن يرى الزبون روابط التقييمات السلبية.',
                ],
                [
                    'keywords' => ['توجيه المشاعر', 'sentiment routing', 'المشاعر الذكية'],
                    'answer' => 'التوجيه الذكي يفرّق الآراء: الإيجابية تُوجَّه لتقييمات Google، والسلبية تُحوَّل إلى شكاوى داخلية حتى يتم حلها بسرية.',
                ],
                [
                    'keywords' => ['الباقات', 'خطة', 'خطط', 'سعر', 'تكلفة', 'أسعار', 'الأسعار', 'بكم', 'قديش', 'كم', 'اشتراكات', 'الاشتراكات', 'اشتراك', 'الاشتراك'],
                    'answer' => 'نقدم Starter مجاناً لفرع واحد، Growth Team بـ30$ شهرياً لثلاثة فروع، وEnterprise/Scale بـ65$ شهرياً لأكثر من 10 فروع.',
                ],
                [
                    'keywords' => ['تفعيل الحساب', 'بعد الدفع', 'التفعيل', 'webhook', 'لم تظهر الميزات', 'تأخر التفعيل', 'التفعيل ما طلع', 'لم يتم التفعيل', 'ما انفع', 'ما اشتغل'],
                    'answer' => 'التفعيل يتم فور إتمام الدفع عبر Lemon Squeezy، حيث ترسل بوابة الدفع Webhook ويُفتح حسابك تلقائياً داخل لوحة التحكم.',
                ],
                [
                    'keywords' => ['بطاقة ائتمان', 'بدون بطاقة', 'التجربة'],
                    'answer' => 'يمكنك البدء في باقة Starter المجانية بدون بطاقة ائتمان، وتجربة المنصة مباشرة دون أي التزام مالي.',
                ],
                [
                    'keywords' => ['ترقية', 'إلغاء', 'خفض', 'تغيير الباقة'],
                    'answer' => 'يمكنك ترقية أو خفض أو إلغاء الباقة في أي وقت من صفحة الفواتير داخل لوحة التحكم.',
                ],
                [
                    'keywords' => ['qr', 'كود qr', 'رمز qr', 'الفرع', 'الكود', 'باركود', 'مسح', 'ماسح', 'scan', 'رابط qr'],
                    'answer' => 'بعد إضافة اسم الفرع وموقعه، يُولد النظام رابطاً ورمز QR فريداً يمكنك تحميله وطباعته مباشرة.',
                ],
                [
                    'keywords' => ['تخصيص', 'هوية الشركة', 'العلامة التجارية', 'الشعار'],
                    'answer' => 'صفحات الهبوط قابلة للتخصيص بالكامل بشعارك وألوانك لتبدو كجزء من هوية شركتك.',
                ],
                [
                    'keywords' => ['صلاحيات', 'صلاحية', 'مدراء', 'المدراء', 'الفريق', 'توزيع الصلاحيات', 'مدير فرع', 'ادارة الفريق'],
                    'answer' => 'يمكنك إضافة مدراء وتعيين صلاحيات لكل فرع، بحيث يرى كل مدير شكاوى وتقارير فرعه فقط.',
                ],
                [
                    'keywords' => ['غير متاحة', 'صيانة', 'صفحة صيانة', 'خطأ مؤقت', 'مسحت الكود', 'رمز qr لا يعمل', 'لم تعمل الصفحة', 'غير متاحة مؤقتاً'],
                    'answer' => 'عند انتهاء الاشتراك أو تجاوز الحدود، يظهر للزائر صفحة صيانة روتينية بينما يتلقى المالك تنبيه داخلي دون كشف المشكلة للعميل.',
                ],
                [
                    'keywords' => ['تأخر التفعيل', 'لم تظهر الميزات', 'بعد الدفع', 'الخصائص'],
                    'answer' => 'عادةً التفعيل فوري. إن استمرت المشكلة أكثر من 5 دقائق، استخدم دعم البوت لإرسال رقم المعاملة لتفعيل يدوي سريع.',
                ],
                [
                    'keywords' => ['تنبيهات', 'إيميل', 'شكاوى', 'بريل'],
                    'answer' => 'تأكد من إعداد بريد مدير الفرع بشكل صحيح في صفحة الفريق وتحقق من صندوق الرسائل غير المرغوب فيها إذا لم يصل الإشعار.',
                ],
                [
                    'keywords' => ['أمن البيانات', 'الخصوصية', 'بيانات العملاء', 'مخفية'],
                    'answer' => 'نحتفظ ببيانات العملاء بأمان داخل قاعدة البيانات ولا نشاركها أو نبيعها لأي طرف خارجي. تُستخدم فقط لتسليم التقارير وتنبيهات العمليات.',
                ],
                [
                    'keywords' => ['البوت لم يفهم', 'لا يفهم', 'شخص حقيقي', 'الاتصال بالدعم'],
                    'answer' => 'إذا لم يكن البوت كافياً، اضغط على خيار الدعم البشري وأرسل المشكلة. سيتم حفظ التذكرة وإرسالها إلى info.zaynix@gmail.com.',
                ],
            ];
        }

        return [
            [
                'keywords' => ['how does revshieldra', 'protect my reputation', 'protect reputation', 'reputation shield'],
                'answer' => 'RevShieldra protects your reputation by routing happy customers to Google and capturing negative feedback internally so public negative reviews are avoided.',
            ],
            [
                'keywords' => ['sentiment routing', 'smart routing', 'how it works'],
                'answer' => 'Sentiment routing directs positive ratings to Google and keeps neutral or negative feedback inside a private complaint workflow for discreet resolution.',
            ],
            [
                'keywords' => ['plans', 'pricing', 'subscription', 'package'],
                'answer' => 'Starter is free for one location, Growth Team is $30/mo for three locations, and Enterprise/Scale is $65/mo for 10+ locations.',
            ],
            [
                'keywords' => ['activate account', 'after payment', 'webhook', 'payment'],
                'answer' => 'Once payment completes via Lemon Squeezy, a webhook fires and the new plan features unlock automatically inside the dashboard.',
            ],
            [
                'keywords' => ['credit card', 'no credit card', 'trial'],
                'answer' => 'You can start with the free Starter plan without a credit card and test the platform before upgrading.',
            ],
            [
                'keywords' => ['upgrade', 'downgrade', 'cancel plan', 'change plan'],
                'answer' => 'You can upgrade, downgrade, or cancel at any time from the billing settings inside the dashboard.',
            ],
            [
                'keywords' => ['qr code', 'qr', 'branch qr', 'location qr'],
                'answer' => 'When you add a branch, the system generates a unique link and QR code for that location, ready to print and use immediately.',
            ],
            [
                'keywords' => ['white label', 'branding', 'custom page', 'logo'],
                'answer' => 'Yes, review landing pages can be branded with your logo and colors, so the experience feels native to your business.',
            ],
            [
                'keywords' => ['permissions', 'team roles', 'branch manager', 'access'],
                'answer' => 'You can assign managers to specific locations so each leader only sees complaints and reports for their own branch.',
            ],
            [
                'keywords' => ['maintenance page', 'temporarily unavailable', 'subscription expired'],
                'answer' => 'If a subscription expires or limits are exceeded, the customer sees a routine maintenance page while only you see an internal alert to renew.',
            ],
            [
                'keywords' => ['activation delay', 'features not showing', 'after payment not active'],
                'answer' => 'Activation is usually immediate. If it takes more than 5 minutes, submit a support ticket with the transaction details for a fast manual review.',
            ],
            [
                'keywords' => ['email alerts', 'notifications', 'complaint email'],
                'answer' => 'Make sure each branch manager email is correct in the team settings and check spam/junk if alerts are not arriving.',
            ],
            [
                'keywords' => ['data security', 'privacy', 'customer data', 'encrypted'],
                'answer' => 'Customer and complaint data are stored securely and never shared. It is used only for reporting and internal alerts within your business.',
            ],
            [
                'keywords' => ['bot didn’t understand', 'not clear', 'human support', 'contact support'],
                'answer' => 'If the bot can’t answer, click Talk to human support and submit your issue. It will be saved and sent to info.zaynix@gmail.com.',
            ],
        ];
    }

    private function getFlowKnowledgeBase(string $language): string
    {
        if ($language === 'ar') {
            return <<<'AR'
Root Menu:
- Flow 1: التعرف على المنصة وآلية العمل الذكية.
- Flow 2: الأسعار، الخطط، وتفاصيل الاشتراك والدفع.
- Flow 3: إعداد الفروع، الـ QR، وصلاحيات الفريق.
- Flow 4: حل المشاكل التقنية وتنبيهات الحساب.
- Flow 5: أمان البيانات والتواصل المباشر مع الإدارة.

Flow 1: التعرف على المنصة وآلية العمل الذكية:
RevShieldra هو نظام ذكي مخصص للشركات متعددة الفروع لإدارة السمعة الرقمية من لوحة تحكم واحدة.
العمل: يفرز التقييمات عبر مسح QR. العملاء الراضون (4-5 نجوم) يُوجَّهون تلقائيًا إلى Google Maps. العملاء غير الراضين (3 نجوم أو أقل) يُحتجزون في نموذج شكوى داخلي وسري لتصلك المشكلة دون أن يرى العميل روابط جوجل.

Flow 2: الأسعار، الخطط، والاشتراك:
باقة Starter مجانية لموقع واحد، باقة Growth Team 30$ شهريًا لثلاثة فروع (الشهر الأول مجاني)، وباقة Enterprise/Scale 65$ شهريًا لأكثر من 10 فروع.
جميع الدفع الآمن يتم عبر Lemon Squeezy، والبدء الفوري يتم عن طريق Webhook يفتح الميزات تلقائيًا.
يمكنك الترقية أو التخفيض أو الإلغاء في أي وقت من إعدادات الفواتير.

Flow 3: إعداد الفروع، الـ QR، والصلاحيات:
بمجرد إضافة الفرع وموقعه، ينشئ النظام رابطًا ورمز QR فريدين قابلين للتحميل والطباعة.
الصفحات مخصصة بعلامتك التجارية، بما في ذلك الشعار والألوان.
يمكنك توزيع صلاحيات الفريق بوضوح؛ كل مدير يرى تقارير وشكاوى فرعه فقط.

Flow 4: حل المشاكل التقنية وحالات الحساب:
إذا ظهرت صفحة صيانة أو غير متاحة مؤقتًا فذلك لحماية سمعتك عند انتهاء الاشتراك أو تجاوز الحدود. العميل لا يرى المشكلة الحقيقية، وأنت ترى تنبيهًا داخليًا للتجديد.
إذا لم تظهر الميزات بعد الدفع، فإن التفعيل يكون عادةً فوريًا. في حالات نادرة، انتظر حتى 5 دقائق ثم افتح تذكرة دعم.
تأكد من إعداد بريد كل مدير فرع في صفحة الفريق وتحقق من مجلد Spam إذا لم تصل التنبيهات.

Flow 5: الأمان، الخصوصية والدعم البشري:
بيانات العملاء والشكاوى محفوظة بأمان، ولا يتم مشاركتها أو بيعها لأي طرف خارجي. تُستخدم فقط لتسليم التقارير وتنبيهات فريقك.
إذا تعذر على البوت الإجابة، اطلب دعم بشري عبر الزر المخصص، وسنرسل التذكرة إلى info.zaynix@gmail.com بسرعة.
AR;
        }

        return <<<'EN'
Root Menu:
- Flow 1: Concept & smart routing.
- Flow 2: Pricing, plans, subscription, and payment.
- Flow 3: Setup, QR codes, and team permissions.
- Flow 4: Troubleshooting and account alerts.
- Flow 5: Security, privacy, and human fallback.

Flow 1: Concept & Routing:
RevShieldra is a smart reputation system for multi-location businesses managed from one dashboard.
How it works: Reviews are categorized via QR scanning. Happy customers (4-5 stars) are routed to Google Maps. Unsatisfied feedback (3 stars or less) is captured into a private complaint form for branch managers without showing Google links.

Flow 2: Pricing & Lemon Squeezy:
Starter is free for one location, Growth Team is $30/mo for three locations with the first month free, and Enterprise/Scale is $65/mo for 10+ locations.
Payments are secure through Lemon Squeezy, and a webhook instantly unlocks premium features in the dashboard.
You can upgrade, downgrade, or cancel anytime from billing settings.

Flow 3: Operations & Setup:
After adding a branch and location, the system generates a unique link and QR code ready to download and print.
Landing pages are fully brandable with your logo and colors.
Team permissions can be assigned so each branch manager sees only their branch reports and complaints.

Flow 4: Technical & Troubleshooting:
A maintenance page appears when a subscription expires or branch limits are exceeded to protect your reputation. Customers see a routine update message while you get an internal renewal alert.
Activation is usually instant. If features do not appear after payment, wait 5 minutes and submit a support ticket with the transaction details.
Make sure branch managers' email addresses are correct and check spam/junk for complaint notifications.

Flow 5: Security & Fallback:
Customer and complaint data are stored securely and are not shared with external parties. They are used only for internal reporting and service operations.
If the bot cannot answer, use the human support option to submit your issue. The ticket will be saved and sent to info.zaynix@gmail.com for fast response.
EN;
    }

    private function isSupportRequest(string $message, string $language): bool
    {
        $message = mb_strtolower($message);
        $supportKeywords = $language === 'ar'
            ? ['مساعدة', 'الدعم', 'بشري', 'تحدث مع', 'لا تعمل', 'لا يمكنني', 'contact support', 'طلب دعم']
            : ['support', 'help', 'human', 'agent', 'cannot', 'can’t', 'not working', 'contact', 'ticket'];

        foreach ($supportKeywords as $keyword) {
            if (Str::contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function generateBotResponse(string $message, string $language): array
    {
        $systemPrompt = $language === 'ar'
            ? "أنت المساعد الذكي الرسمي لـ RevShieldra. لديك قدرة فائقة على فهم اللهجات العربية والأخطاء الإملائية والتعبيرات العامية. إذا كان السؤال قريبًا من أي موضوع داخل الـ Flows الخمسة – مثل الأسعار، الاشتراكات، التفعيل، QR، إعداد الفروع، صلاحيات الفريق، التنبيهات، صفحة الصيانة، أو الأمان – فأجب مباشرةً دون إرسال المستخدم إلى الدعم البشري، حتى لو كانت الصياغة غير واضحة قليلاً. استخدم fallback=true فقط عندما يكون السؤال خارج نطاق Flows الخمسة تمامًا أو عندما يطلب المستخدم دعمًا بشريًا صريحًا. أجب فقط بصيغة JSON خام بدون أي أكواد أو شروحات إضافية. يجب أن يحتوي الناتج على الحقول: reply وfallback."
            : "You are the official RevShieldra smart assistant. You have excellent NLP capability and understand dialects, typos, and similar-sounding phrases. If the question is close to any of the five Flows – like pricing, plans, activation, QR, branch setup, team permissions, alerts, maintenance page, or security – answer directly and do not send the user to human support, even if the wording is slightly unclear. Use fallback=true only when the question is clearly outside the five Flows or when the user explicitly asks for human support. Reply only in raw JSON with the fields reply and fallback.";

        $flowKnowledge = $this->getFlowKnowledgeBase($language);
        $prompt = $systemPrompt . "\n\n" . $flowKnowledge . "\n\nUser message: {$message}\nReturn only a JSON object with reply and fallback.";

        $apiKey = env('GEMINI_API_KEY');
        $model = env('GEMINI_MODEL', 'gemini-flash-latest');

        if ($apiKey) {
            try {
                $response = Http::timeout(12)->withHeaders(['Content-Type' => 'application/json'])
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'temperature' => 0.2,
                            'maxOutputTokens' => 450,
                            'responseMimeType' => 'application/json',
                        ],
                    ]);

                $body = $response->json();
                $botText = data_get($body, 'candidates.0.content.parts.0.text')
                    ?? data_get($body, 'candidates.0.content.0.text')
                    ?? data_get($body, 'candidates.0.output.0.content.0.text');

                if ($response->successful() && $botText) {
                    $decoded = json_decode(trim($botText), true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['reply'])) {
                        return [
                            'reply' => trim($decoded['reply']),
                            'fallback' => isset($decoded['fallback']) ? (bool) $decoded['fallback'] : false,
                        ];
                    }

                    $cleaned = preg_replace('/^```(?:json)?\s*/', '', trim($botText));
                    $cleaned = preg_replace('/\s*```$/', '', $cleaned);
                    $decoded = json_decode($cleaned, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['reply'])) {
                        return [
                            'reply' => trim($decoded['reply']),
                            'fallback' => isset($decoded['fallback']) ? (bool) $decoded['fallback'] : false,
                        ];
                    }

                    Log::warning('Chatbot Gemini returned invalid JSON response', ['text' => $botText]);
                    return [
                        'reply' => $language === 'ar'
                            ? 'أعتذر، لم أفهم سؤالك بشكل كامل. يمكنك استخدام الدعم الفني أو العودة إلى القائمة الرئيسية.'
                            : 'Sorry, I did not fully understand your question. You can use human support or return to the main menu.',
                        'fallback' => true,
                    ];
                }
            } catch (\Exception $exception) {
                Log::warning('Chatbot Gemini fallback: ' . $exception->getMessage());
            }
        }

        return [
            'reply' => $language === 'ar'
                ? 'أعتذر، لم أفهم سؤالك بشكل كامل. يمكنك استخدام الدعم الفني أو العودة إلى القائمة الرئيسية.'
                : 'Sorry, I did not fully understand your question. You can use human support or return to the main menu.',
            'fallback' => true,
        ];
    }

    /**
     * Handle showing root menu (flow action)
     */
    private function handleFlowAction(Request $request, string $sessionId, ?int $userId, ?string $userEmail, string $lang): \Illuminate\Http\JsonResponse
    {
        $flows = ChatbotFlowService::getFlows($lang);
        $rootMessage = $flows['root']['message'];

        ChatbotLog::create([
            'user_id' => $userId,
            'user_email' => $userEmail,
            'session_id' => $sessionId,
            'sender' => 'bot',
            'message' => $rootMessage,
            'language' => $lang,
            'log_type' => 'chat',
        ]);

        return response()->json([
            'reply' => $rootMessage,
            'flow' => 'root',
            'show_menu' => ChatbotFlowService::getRootMenu($lang),
            'message_type' => 'separator'
        ]);
    }

    /**
     * Handle navigating to a flow branch
     */
    private function handleNavigateAction(Request $request, string $sessionId, ?int $userId, ?string $userEmail, string $lang): \Illuminate\Http\JsonResponse
    {
        $flowKey = $request->input('flow_key');
        $branchKey = $request->input('branch_key');

        if (!$flowKey) {
            return response()->json([
                'error' => true,
                'message' => $lang === 'ar' ? 'بيانات المسار غير صحيحة.' : 'Invalid flow path.'
            ], 400);
        }

        // Case 1: Only flow_key provided - return list of branches
        if (!$branchKey) {
            $flows = ChatbotFlowService::getFlows($lang);
            $flowData = $flows['root']['flows'][$flowKey] ?? null;

            if (!$flowData) {
                return response()->json([
                    'error' => true,
                    'message' => $lang === 'ar' ? 'المسار غير موجود.' : 'Flow not found.'
                ], 404);
            }

            $branches = $flowData['branches'] ?? [];
            $branchList = [];
            foreach ($branches as $key => $branch) {
                $branchList[$key] = [
                    'label' => $branch['label'],
                    'category' => $branch['category'] ?? 'general'
                ];
            }

            return response()->json([
                'reply' => $flowData['label'],
                'flow' => 'flow_menu',
                'flow_key' => $flowKey,
                'branches' => $branchList,
                'message_type' => 'branch_list'
            ]);
        }

        // Case 2: Both flow_key and branch_key provided - return full branch response
        $branches = ChatbotFlowService::getFlowBranches($lang, $flowKey);
        if (!isset($branches[$branchKey])) {
            return response()->json([
                'error' => true,
                'message' => $lang === 'ar' ? 'المسار غير موجود.' : 'Branch not found.'
            ], 404);
        }

        $branch = $branches[$branchKey];
        $response = $branch['response'];
        $category = $branch['category'] ?? 'general';

        ChatbotLog::create([
            'user_id' => $userId,
            'user_email' => $userEmail,
            'session_id' => $sessionId,
            'sender' => 'user',
            'message' => $branchKey,
            'language' => $lang,
            'log_type' => 'flow_navigation',
            'metadata' => json_encode(['flow' => $flowKey, 'branch' => $branchKey, 'category' => $category])
        ]);

        ChatbotLog::create([
            'user_id' => $userId,
            'user_email' => $userEmail,
            'session_id' => $sessionId,
            'sender' => 'bot',
            'message' => $response,
            'language' => $lang,
            'log_type' => 'chat',
            'metadata' => json_encode(['flow' => $flowKey, 'branch' => $branchKey, 'category' => $category])
        ]);

        // Store flow context in session for later use with tickets
        if ($session = session()) {
            $session->put("chatbot_flow_{$sessionId}", [
                'flow' => $flowKey,
                'branch' => $branchKey,
                'category' => $category
            ]);
        }

        // Get flow tree to find sub options
        $flows = ChatbotFlowService::getFlows($lang);
        $branchData = $flows['root']['flows'][$flowKey]['branches'][$branchKey] ?? null;
        $subOptions = $branchData['sub_options'] ?? [];

        return response()->json([
            'reply' => $response,
            'flow' => 'branch',
            'flow_key' => $flowKey,
            'branch_key' => $branchKey,
            'category' => $category,
            'sub_options' => $subOptions,
            'message_type' => 'branch_answer'
        ]);
    }

    /**
     * Handle back navigation
     */
    private function handleBackAction(Request $request, string $sessionId, ?int $userId, ?string $userEmail, string $lang): \Illuminate\Http\JsonResponse
    {
        $level = $request->input('level', 'root'); // 'root' or 'flow'

        if ($level === 'root') {
            return $this->handleFlowAction($request, $sessionId, $userId, $userEmail, $lang);
        }

        // Go back to flow menu from branch
        $flowKey = $request->input('flow_key');
        if (!$flowKey) {
            return $this->handleFlowAction($request, $sessionId, $userId, $userEmail, $lang);
        }

        $flows = ChatbotFlowService::getFlows($lang);
        $flowData = $flows['root']['flows'][$flowKey] ?? null;

        if (!$flowData) {
            return $this->handleFlowAction($request, $sessionId, $userId, $userEmail, $lang);
        }

        $flowLabel = $flowData['label'];
        $branches = [];

        foreach ($flowData['branches'] as $key => $branch) {
            $branches[$key] = [
                'label' => $branch['label']
            ];
        }

        $message = $lang === 'ar'
            ? "⬅️ رجعت إلى القائمة الفرعية: **{$flowLabel}**\n\nاختر أحد الخيارات التالية:"
            : "⬅️ Back to: **{$flowLabel}**\n\nChoose one of the options below:";

        ChatbotLog::create([
            'user_id' => $userId,
            'user_email' => $userEmail,
            'session_id' => $sessionId,
            'sender' => 'bot',
            'message' => $message,
            'language' => $lang,
            'log_type' => 'chat',
            'metadata' => json_encode(['action' => 'back', 'flow' => $flowKey])
        ]);

        return response()->json([
            'reply' => $message,
            'flow' => 'flow_menu',
            'flow_key' => $flowKey,
            'branches' => $branches,
            'message_type' => 'separator'
        ]);
    }

    /**
     * Override ticket submission to include flow context
     */
    private function submitTicketWithContext(Request $request, string $sessionId, ?int $userId, ?string $userEmail, string $lang): \Illuminate\Http\JsonResponse
    {
        $emailInput = $request->input('email');
        $issueContent = $request->input('message');
        $nameInput = $request->input('name', $request->input('email'));
        $category = $request->input('category', 'general');

        // Get flow context if exists
        $flowContext = ($s = session()) ? $s->get("chatbot_flow_{$sessionId}", []) : [];

        ChatbotLog::create([
            'user_id' => $userId,
            'user_email' => $emailInput,
            'session_id' => $sessionId,
            'sender' => 'user',
            'message' => "Support ticket submitted: {$issueContent}",
            'language' => $lang,
            'log_type' => 'ticket_request',
            'metadata' => json_encode(['category' => $category, 'flow_context' => $flowContext])
        ]);

        $ticketData = [
            'name' => $nameInput,
            'email' => $emailInput,
            'issue' => $issueContent,
            'is_logged_in' => auth()->check(),
            'category' => $category,
            'flow_context' => $flowContext,
        ];

        try {
            Mail::to('info.zaynix@gmail.com')->send(new SupportTicketMail($ticketData));

            $botReply = $lang === 'ar'
                ? 'تم استلام طلبك بنجاح، وسيتواصل معنا فريق الدعم الفني معك عبر البريد الإلكتروني قريبًا.'
                : 'Your support request has been received successfully. Our team will contact you shortly via email.';

            ChatbotLog::create([
                'user_id' => $userId,
                'user_email' => $userEmail,
                'session_id' => $sessionId,
                'sender' => 'bot',
                'message' => $botReply,
                'language' => $lang,
                'log_type' => 'chat',
            ]);

            return response()->json(['reply' => $botReply, 'flow' => 'end']);
        } catch (\Exception $exception) {
            Log::error('Chatbot ticket email failed: ' . $exception->getMessage());

            return response()->json([
                'reply' => $lang === 'ar'
                    ? 'عذراً، حدث خطأ أثناء إرسال التذكرة. الرجاء المحاولة لاحقًا أو الاتصال مباشرة عبر info.zaynix@gmail.com.'
                    : 'Sorry, there was an error sending your ticket. Please try again later or contact info.zaynix@gmail.com directly.',
            ], 500);
        }
    }

    private function detectLearnedFlowFromMessage(string $message, string $language): ?array
    {
        $normalizedMessage = $this->normalizeLearningText($message, $language);
        if ($normalizedMessage === '') {
            return null;
        }

        $learnedKeywords = ChatbotLearnedKeyword::query()
            ->where('language', $language)
            ->orderByRaw('LENGTH(normalized_keyword) DESC')
            ->limit(200)
            ->get();

        foreach ($learnedKeywords as $learnedKeyword) {
            $keyword = (string) $learnedKeyword->normalized_keyword;

            if ($keyword === '') {
                continue;
            }

            if ($normalizedMessage === $keyword || Str::contains($normalizedMessage, $keyword)) {
                $learnedKeyword->forceFill(['last_used_at' => now()])->save();

                return [
                    'flow' => $learnedKeyword->target_flow,
                    'branch' => $learnedKeyword->target_branch,
                    'custom_response' => $learnedKeyword->custom_response,
                    'source' => 'learned_keyword',
                    'keyword' => $learnedKeyword->keyword,
                ];
            }
        }

        return null;
    }

    private function storeUnhandledQuery(string $message, string $language, array $metadata = []): void
    {
        $normalizedMessage = $this->normalizeLearningText($message, $language);

        if (mb_strlen($normalizedMessage) < 3) {
            return;
        }

        try {
            $query = ChatbotUnhandledQuery::firstOrNew([
                'language' => $language,
                'normalized_query' => mb_substr($normalizedMessage, 0, 500),
            ]);

            $query->query = $message;
            $query->status = 'pending';
            $query->last_seen_at = now();
            $query->metadata = array_filter($metadata, fn($value) => $value !== null);

            if ($query->exists) {
                $query->occurrences = $query->occurrences + 1;
            }

            $query->save();
        } catch (\Throwable $exception) {
            Log::warning('Failed to store chatbot unhandled query: ' . $exception->getMessage());
        }
    }

    private function normalizeLearningText(string $text, string $language): string
    {
        $text = trim(mb_strtolower($text));

        if ($language === 'ar') {
            $text = str_replace(['أ', 'إ', 'آ', 'ى', 'ئ', 'ؤ', 'ة', 'ـ'], ['ا', 'ا', 'ا', 'ي', 'ي', 'و', 'ه', ''], $text);
        }

        return Str::squish($text);
    }

    /**
     * Detect which flow the user's message matches
     * Returns array with 'flow' and optional 'branch' keys if matched
     */
    private function detectFlowFromMessage(string $message, string $language): ?array
    {
        $message = mb_strtolower($message);
        if ($language === 'ar') {
            $message = str_replace(['أ', 'إ', 'آ', 'ى', 'ئ', 'ؤ', 'ة', 'ـ'], ['ا', 'ا', 'ا', 'ي', 'ي', 'و', 'ه', ''], $message);
            $message = $this->normalizeArabicText($message);
        }

        $directMatches = $language === 'ar' ? [
            'platform' => [
                'about' => ['كيف بتشتغل منصتكم', 'كيف تشتغل منصتكم', 'كيف تعمل منصتكم', 'كيف المنصه بتشتغل', 'كيف المنصة بتشتغل', 'كيف بتشتغل المنصه', 'كيف بتشتغل المنصة', 'كيف يعمل النظام', 'اشرح المنصه', 'اشرح المنصة'],
                'smart_routing' => ['كيف بتوجه التقييمات', 'كيف توجه التقييمات', 'كيف تمنع التقييم السلبي', 'كيف فلتره التقييمات'],
            ],
        ] : [
            'platform' => [
                'about' => ['how does your platform work', 'how does the platform work', 'how revshieldra works'],
                'smart_routing' => ['how review gating works', 'how smart routing works'],
            ],
        ];

        foreach ($directMatches as $flowKey => $branches) {
            foreach ($branches as $branchKey => $keywords) {
                foreach ($keywords as $keyword) {
                    $normalizedKeyword = mb_strtolower($keyword);
                    if ($language === 'ar') {
                        $normalizedKeyword = str_replace(['أ', 'إ', 'آ', 'ى', 'ئ', 'ؤ', 'ة', 'ـ'], ['ا', 'ا', 'ا', 'ي', 'ي', 'و', 'ه', ''], $normalizedKeyword);
                    }

                    if ($this->messageContainsKeyword($message, $normalizedKeyword, $language)) {
                        return ['flow' => $flowKey, 'branch' => $branchKey];
                    }
                }
            }
        }

        $branchKeywords = $language === 'ar' ? [
            'pricing' => [
                'plans' => ['سعر', 'اسعار', 'باقات', 'باقة', 'خطة', 'خطط', 'اشتراك', 'تكلفة', 'بكم', 'قديش', 'مجاني', 'basic', 'pro'],
                'payment' => ['دفع', 'بطاقة', 'فاتورة', 'تجديد', 'خصم', 'بوابة الدفع', 'lemon'],
                'upgrade' => ['ترقية', 'الغاء', 'إلغاء', 'خفض', 'تغيير الخطة', 'cancel', 'upgrade'],
            ],
            'platform' => [
                'about' => ['ما هي', 'شو هي', 'revshieldra', 'المنصة', 'النظام', 'مميزات', 'اشرح'],
                'smart_routing' => ['توجيه', 'مشاعر', 'فلترة', 'review gating', 'تقييم سلبي', 'تقييم ايجابي', 'google reviews'],
            ],
            'setup' => [
                'create_branch' => ['فرع', 'اضافة فرع', 'إنشاء فرع', 'انشاء فرع', 'موقع', 'location'],
                'qr_generation' => ['qr', 'كيو ار', 'باركود', 'رمز', 'كود', 'مسح', 'scan'],
                'branch_limits' => ['حدود الفروع', 'كم فرع', 'عدد الفروع', 'فروع الخطة'],
            ],
            'security' => [
                'data_protection' => ['امان', 'أمان', 'حماية', 'خصوصية', 'تشفير', 'بيانات العملاء'],
                'data_retention' => ['حذف البيانات', 'نسخ احتياطي', 'backup', 'احتفاظ', 'اغلاق الحساب'],
            ],
            'troubleshooting' => [
                'billing_issues' => ['مشكلة دفع', 'فاتورة', 'الدفع ما اشتغل', 'بعد الدفع', 'لم تتفعل', 'لم تظهر الميزات', 'billing'],
                'technical_issues' => ['خطأ', 'error', '400', 'failed to load', 'لا يعمل', 'ما بشتغل', 'بطيء', 'خلل', 'الرابط لا يعمل', 'qr لا يعمل'],
            ],
        ] : [
            'pricing' => [
                'plans' => ['price', 'pricing', 'plan', 'plans', 'subscription', 'package', 'cost', 'how much', 'free', 'basic', 'pro'],
                'payment' => ['payment', 'card', 'billing', 'invoice', 'renewal', 'charge', 'lemon'],
                'upgrade' => ['upgrade', 'downgrade', 'cancel', 'change plan'],
            ],
            'platform' => [
                'about' => ['what is', 'revshieldra', 'platform', 'features', 'explain', 'purpose'],
                'smart_routing' => ['smart routing', 'review gating', 'sentiment', 'negative review', 'positive review', 'google reviews'],
            ],
            'setup' => [
                'create_branch' => ['branch', 'create branch', 'location', 'add location'],
                'qr_generation' => ['qr', 'code', 'barcode', 'scan'],
                'branch_limits' => ['branch limit', 'limits', 'how many branches', 'locations limit'],
            ],
            'security' => [
                'data_protection' => ['security', 'safe', 'privacy', 'encrypt', 'customer data'],
                'data_retention' => ['delete data', 'backup', 'retention', 'close account'],
            ],
            'troubleshooting' => [
                'billing_issues' => ['billing issue', 'payment issue', 'invoice issue', 'plan did not update', 'charged'],
                'technical_issues' => ['problem', 'error', '400', 'failed to load', 'not working', 'bug', 'slow', 'link error', 'qr not working'],
            ],
        ];

        $bestMatch = null;
        $bestScore = 0;

        foreach ($branchKeywords as $flowKey => $branches) {
            foreach ($branches as $branchKey => $keywords) {
                $score = 0;
                foreach ($keywords as $keyword) {
                    $normalizedKeyword = mb_strtolower($keyword);
                    if ($language === 'ar') {
                        $normalizedKeyword = $this->normalizeArabicText($normalizedKeyword);
                    }

                    if ($this->messageContainsKeyword($message, $normalizedKeyword, $language)) {
                        $score += mb_strlen($normalizedKeyword) > 8 ? 2 : 1;
                    }
                }

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = ['flow' => $flowKey, 'branch' => $branchKey];
                }
            }
        }

        if ($bestMatch) {
            return $bestMatch;
        }

        // Define flow keywords
        $flowKeywords = $language === 'ar' ? [
            'pricing' => ['سعر', 'خطة', 'اشتراك', 'باقة', 'تكلفة', 'الاشتراك', 'الخطط', 'بكم', 'قديش', 'كم السعر', 'الأسعار', 'الباقات', 'دفع', 'بيانات البطاقة', 'فاتورة', 'الدفع', 'ترقية', 'إلغاء', 'خفض الخطة'],
            'platform' => ['كيف يعمل', 'اشرح', 'كيفية العمل', 'مميزات', 'المنصة', 'revshieldra', 'الهدف', 'ما هي', 'نظام', 'آلية'],
            'setup' => ['فرع', 'branch', 'qr', 'رمز', 'كود', 'باركود', 'إضافة فرع', 'إنشاء فرع', 'الفروع', 'روابط', 'الروابط', 'فريق', 'مدراء', 'صلاحيات'],
            'security' => ['أمان', 'بيانات', 'خصوصية', 'حماية', 'تشفير', 'آمن', 'الخصوصية', 'حذف البيانات', 'النسخ الاحتياطي'],
            'troubleshooting' => ['مشكلة', 'خطأ', 'error', 'لا يعمل', 'صيانة', 'مسحت', 'توقفت', 'خلل', 'بطيء', 'لم تظهر الميزات', 'ما اشتغل']
        ] : [
            'pricing' => ['price', 'pricing', 'plan', 'subscription', 'package', 'cost', 'plans', 'how much', 'payment', 'card', 'billing', 'invoice', 'upgrade', 'downgrade', 'cancel'],
            'platform' => ['how', 'explain', 'how it works', 'feature', 'features', 'revshieldra', 'what is', 'purpose', 'system', 'works'],
            'setup' => ['branch', 'qr', 'code', 'barcode', 'create branch', 'locations', 'links', 'team', 'manager', 'permission'],
            'security' => ['security', 'data', 'privacy', 'safe', 'encrypt', 'delete', 'backup', 'safe'],
            'troubleshooting' => ['problem', 'error', 'not working', 'maintenance', 'issue', 'bug', 'slow', 'failed', 'doesn\'t work', 'failed to load']
        ];

        foreach ($flowKeywords as $flowKey => $keywords) {
            foreach ($keywords as $keyword) {
                $normalizedKeyword = mb_strtolower($keyword);
                if ($language === 'ar') {
                    $normalizedKeyword = $this->normalizeArabicText($normalizedKeyword);
                }

                if ($this->messageContainsKeyword($message, $normalizedKeyword, $language)) {
                    return ['flow' => $flowKey];
                }
            }
        }

        return null;
    }
}

