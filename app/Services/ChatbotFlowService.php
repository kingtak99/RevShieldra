<?php

namespace App\Services;

class ChatbotFlowService
{
    /**
     * Get the complete flow tree structure for Arabic
     */
    public static function getArabicFlows(): array
    {
        return [
            'root' => [
                'message' => 'أهلاً بك في RevShieldra! 👋 أنا مساعدك الذكي لإدارة السمعة الرقمية. اختر أحد المسارات التالية:',
                'flows' => [
                    'pricing' => [
                        'label' => '💰 الأسعار وخطط الاشتراك',
                        'emoji' => '💰',
                        'branches' => [
                            'plans' => [
                                'label' => 'التعرف على الخطط المتوفرة',
                                'response' => 'نوفر 3 خطط تناسب الجميع: الخطة المجانية لتجربة الميزات الأساسية، خطة Basic للمشاريع الناشئة، وخطة Pro للمحترفين والشركات التي تحتاج لمرونة كاملة وحدود أعلى.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ العودة للقائمة السابقة'],
                                    'support' => ['label' => '🎫 التواصل مع الدعم لطلب خطة مخصصة']
                                ]
                            ],
                            'payment' => [
                                'label' => 'طرق الدفع وتجديد الاشتراك',
                                'response' => 'يتم معالجة كافة المدفوعات بشكل آمن تماماً عبر بوابة Lemon Squeezy العالمية. يمكنك الدفع باستخدام البطاقات الائتمانية أو الحسابات الإلكترونية المتاحة، ويتم تجديد الاشتراك تلقائياً ويمكنك إلغاؤه في أي وقت من إعدادات حسابك.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ العودة للقائمة السابقة'],
                                    'support' => ['label' => '🎫 هل تواجه مشكلة دفع؟']
                                ]
                            ],
                            'upgrade' => [
                                'label' => 'ترقية أو إلغاء الخطة',
                                'response' => 'يمكنك ترقية خطتك فوراً من لوحة التحكم لتحديث حدود الحساب مباشرة. في حال إلغاء الاشتراك، ستبقى ميزات خطتك الحالية نشطة حتى نهاية فترة الفاتورة الحالية ولن يتم خصم أي مبالغ إضافية.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ العودة للقائمة السابقة']
                                ]
                            ]
                        ]
                    ],
                    'platform' => [
                        'label' => '🚀 التعرف على المنصة وآلية العمل',
                        'emoji' => '🚀',
                        'branches' => [
                            'about' => [
                                'label' => 'ما هي منصة RevShieldra؟',
                                'response' => 'RevShieldra هي منصة تسويقية ذكية متخصصة في إدارة السمعة الرقمية وتحسين تقييمات العملاء، من خلال توجيه ذكي للمراجعات وبناء روابط أصول تفاعلية لحماية وتنمية عملك.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ العودة للقائمة السابقة']
                                ]
                            ],
                            'smart_routing' => [
                                'label' => 'كيف تعمل ميزة التوجيه الذكي للمراجعات؟',
                                'response' => 'تعتمد المنصة على نظام فحص ذكي (Review Gating)؛ حيث يتم توجيه العميل السعيد (أصحاب التقييمات الإيجابية) مباشرة إلى منصات التقييم العامة مثل Google لجلب تقييمات حقيقية، بينما يتم توجيه العميل غير الراضي إلى نموذج داخلي خاص لتتمكن من حل مشكلته بشكل خاص قبل أن يتحول لتقييم سلبي علني.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ العودة للقائمة السابقة']
                                ]
                            ]
                        ]
                    ],
                    'setup' => [
                        'label' => '🛠️ إعداد الفروع والروابط الذكية (QR)',
                        'emoji' => '🛠️',
                        'branches' => [
                            'create_branch' => [
                                'label' => 'طريقة إنشاء فرع جديد (Branch)',
                                'response' => 'من لوحة التحكم، انتقل إلى "الفروع"، واضغط على "إضافة فرع جديد". أدخل بيانات الفرع والروابط الخاصة به. تذكر أن لكل خطة اشتراك حداً أقصى لعدد الفروع النشطة.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ العودة للقائمة السابقة'],
                                    'support' => ['label' => '🎫 أحتاج إلى مساعدة']
                                ]
                            ],
                            'qr_generation' => [
                                'label' => 'توليد وتخصيص كود الـ QR',
                                'response' => 'لكل فرع أو رابط أصل تنشئه، تقوم المنصة تلقائياً بتوليد كود QR ديناميكي. يمكنك تحميله، طباعته، أو وضعه في متجرك ليوجه العملاء مباشرة لصفحة التقييم أو العرض الذكي.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ العودة للقائمة السابقة']
                                ]
                            ],
                            'branch_limits' => [
                                'label' => 'حدود الفروع حسب الخطط',
                                'response' => 'تختلف حدود الفروع المتاحة بناءً على نوع خطتك (Free, Basic, Pro). لمعرفة حد حسابك الحالي أو الترقية لزيادة الفروع، انتقل إلى قسم "الاشتراكات" في لوحتك.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ العودة للقائمة السابقة'],
                                    'support' => ['label' => '🎫 أريد الترقية']
                                ]
                            ]
                        ]
                    ],
                    'security' => [
                        'label' => '🔐 أمان البيانات والخصوصية',
                        'emoji' => '🔐',
                        'branches' => [
                            'data_protection' => [
                                'label' => 'كيف تحمون بيانات عملائي؟',
                                'response' => 'نحن نأخذ أمان البيانات بجدية فائقة. جميع البيانات مشفرة بالكامل أثناء النقل والتخزين، ونستخدم بروتوكولات حماية متطورة لضمان عدم وصول أي طرف ثالث لبيانات عملائك أو تقاريرك.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ العودة للقائمة السابقة']
                                ]
                            ],
                            'data_retention' => [
                                'label' => 'سياسة الاحتفاظ بالبيانات والنسخ الاحتياطي',
                                'response' => 'يتم عمل نسخ احتياطي دوري وتلقائي لقواعد البيانات لضمان عدم فقدان أي معلومات. في حال إغلاق الحساب، يمكنك طلب حذف كافة بياناتك نهائياً من سيرفراتنا.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ العودة للقائمة السابقة'],
                                    'support' => ['label' => '🎫 أريد حذف بياناتي']
                                ]
                            ]
                        ]
                    ],
                    'troubleshooting' => [
                        'label' => '🚨 حل المشاكل والمساعدة التقنية',
                        'emoji' => '🚨',
                        'branches' => [
                            'billing_issues' => [
                                'label' => 'مشكلة في بوابة الدفع أو الفواتير',
                                'response' => 'إذا واجهت مشكلة أثناء الدفع أو لم يتم تحديث خطتك بعد الخصم، يرجى تزويدنا بالتفاصيل وسيقوم الفريق المالي بحلها فوراً.',
                                'category' => 'billing',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ العودة للقائمة السابقة'],
                                    'support' => ['label' => '🎫 فتح تذكرة دعم مالي']
                                ]
                            ],
                            'technical_issues' => [
                                'label' => 'مشكلة تقنية في لوحة التحكم أو الروابط',
                                'response' => 'إذا واجهت بطء أو خطأ (Error) أثناء إنشاء فروع أو توليد الروابط، تأكد من استقرار اتصالك أولاً. إذا استمرت المشكلة، يرجى إرسال تفاصيل الخطأ.',
                                'category' => 'technical',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ العودة للقائمة السابقة'],
                                    'support' => ['label' => '🎫 فتح تذكرة دعم تقني']
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get the complete flow tree structure for English
     */
    public static function getEnglishFlows(): array
    {
        return [
            'root' => [
                'message' => 'Welcome to RevShieldra! 👋 I\'m your AI assistant for digital reputation management. Select a path below:',
                'flows' => [
                    'pricing' => [
                        'label' => '💰 Pricing & Subscriptions',
                        'emoji' => '💰',
                        'branches' => [
                            'plans' => [
                                'label' => 'Available Plans',
                                'response' => 'We offer 3 tailored plans: Free (to test basic features), Basic (for growing projects), and Pro (for full flexibility and higher limits).',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ Go Back'],
                                    'support' => ['label' => '🎫 Request Custom Plan']
                                ]
                            ],
                            'payment' => [
                                'label' => 'Payment Methods & Renewal',
                                'response' => 'Payments are securely processed via Lemon Squeezy. We accept major credit cards. Subscriptions renew automatically, and you can cancel anytime from your settings.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ Go Back'],
                                    'support' => ['label' => '🎫 Having Payment Issues?']
                                ]
                            ],
                            'upgrade' => [
                                'label' => 'Upgrade or Cancel Your Plan',
                                'response' => 'You can upgrade or cancel your plan anytime from your billing settings. When you cancel, your current plan remains active until the end of the billing period.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ Go Back']
                                ]
                            ]
                        ]
                    ],
                    'platform' => [
                        'label' => '🚀 Platform Overview & Workflow',
                        'emoji' => '🚀',
                        'branches' => [
                            'about' => [
                                'label' => 'What is RevShieldra?',
                                'response' => 'RevShieldra is a smart SaaS platform designed to manage your digital reputation and boost positive reviews using intelligent asset links and customer filtering.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ Go Back']
                                ]
                            ],
                            'smart_routing' => [
                                'label' => 'How does Smart Review Gating work?',
                                'response' => 'Our system filters feedback: happy customers are routed to public platforms (like Google Reviews), while unsatisfied customers are directed to a private feedback form so you can resolve their issues privately.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ Go Back']
                                ]
                            ]
                        ]
                    ],
                    'setup' => [
                        'label' => '🛠️ Branches & QR Setup',
                        'emoji' => '🛠️',
                        'branches' => [
                            'create_branch' => [
                                'label' => 'Creating a New Branch',
                                'response' => 'Go to \'Branches\' in your dashboard, click \'Add New Branch\', and fill in the details. Note that branch limits depend on your current active plan.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ Go Back'],
                                    'support' => ['label' => '🎫 Need Help?']
                                ]
                            ],
                            'qr_generation' => [
                                'label' => 'Custom QR Code Generation',
                                'response' => 'Every branch automatically generates a dynamic QR code. You can download and print it for your physical store to instantly guide customers.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ Go Back']
                                ]
                            ],
                            'branch_limits' => [
                                'label' => 'Branch Limits by Plan',
                                'response' => 'Branch limits vary by plan type (Free, Basic, Pro). To check your current limit or upgrade for more branches, visit the Subscriptions section in your dashboard.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ Go Back'],
                                    'support' => ['label' => '🎫 Want to Upgrade?']
                                ]
                            ]
                        ]
                    ],
                    'security' => [
                        'label' => '🔐 Data Security & Privacy',
                        'emoji' => '🔐',
                        'branches' => [
                            'data_protection' => [
                                'label' => 'How is my data protected?',
                                'response' => 'Data security is our top priority. All data is fully encrypted in transit and at rest using enterprise-grade security protocols.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ Go Back']
                                ]
                            ],
                            'data_retention' => [
                                'label' => 'Data Retention & Backups',
                                'response' => 'We perform automatic periodic backups to ensure no data loss. If you close your account, you can request complete data deletion from our servers.',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ Go Back'],
                                    'support' => ['label' => '🎫 Delete My Data']
                                ]
                            ]
                        ]
                    ],
                    'troubleshooting' => [
                        'label' => '🚨 Technical Support & Troubleshooting',
                        'emoji' => '🚨',
                        'branches' => [
                            'billing_issues' => [
                                'label' => 'Billing & Payment Issues',
                                'response' => 'If your plan didn\'t update after a successful payment, our team will fix it right away.',
                                'category' => 'billing',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ Go Back'],
                                    'support' => ['label' => '🎫 Open Billing Ticket']
                                ]
                            ],
                            'technical_issues' => [
                                'label' => 'Dashboard or Link Errors',
                                'response' => 'Encountering a bug or error while managing branches? Please let our technical team know.',
                                'category' => 'technical',
                                'sub_options' => [
                                    'back' => ['label' => '⬅️ Go Back'],
                                    'support' => ['label' => '🎫 Open Tech Support Ticket']
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get flow tree for a specific language
     */
    public static function getFlows(string $language): array
    {
        return $language === 'ar' ? self::getArabicFlows() : self::getEnglishFlows();
    }

    /**
     * Navigate to a specific flow path and return the response
     * @param string $language
     * @param array $path - e.g., ['pricing', 'plans']
     */
    public static function navigateFlow(string $language, array $path): ?array
    {
        $flows = self::getFlows($language);
        $current = $flows['root'] ?? null;

        if (!$current) {
            return null;
        }

        foreach ($path as $key) {
            if (isset($current['flows'][$key])) {
                $current = $current['flows'][$key];
            } elseif (isset($current['branches'][$key])) {
                $current = $current['branches'][$key];
            } else {
                return null;
            }
        }

        return $current;
    }

    /**
     * Get root menu options
     */
    public static function getRootMenu(string $language): array
    {
        $flows = self::getFlows($language);
        $menu = [];

        foreach ($flows['root']['flows'] as $key => $flow) {
            $menu[$key] = [
                'label' => $flow['label'],
                'emoji' => $flow['emoji'] ?? ''
            ];
        }

        return $menu;
    }

    /**
     * Get branches for a flow
     */
    public static function getFlowBranches(string $language, string $flowKey): array
    {
        $flows = self::getFlows($language);

        if (!isset($flows['root']['flows'][$flowKey]['branches'])) {
            return [];
        }

        $branches = [];
        foreach ($flows['root']['flows'][$flowKey]['branches'] as $key => $branch) {
            $branches[$key] = [
                'label' => $branch['label'],
                'response' => $branch['response'] ?? '',
                'category' => $branch['category'] ?? null
            ];
        }

        return $branches;
    }
}
