<div id="chatbot-toggle" class="fixed bottom-5 right-5 z-50 flex items-center gap-2 rounded-full bg-slate-900 px-4 py-3 text-white shadow-2xl transition duration-200 hover:scale-105 cursor-pointer">
    <span class="text-xl">💬</span>
    <span id="widget-trigger-text" class="font-semibold text-sm">{{ app()->getLocale() === 'ar' ? 'المساعد الذكي' : 'Smart Assistant' }}</span>
</div>

<div id="chatbot-box" class="hidden fixed bottom-28 right-5 z-50 flex h-[520px] w-[92vw] max-w-xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl md:w-[420px]">
    <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-950 px-4 py-4 text-white">
        <div class="flex items-center gap-3">
            <span class="flex h-3 w-3 rounded-full bg-emerald-400 animate-pulse"></span>
            <div>
                <p class="text-[0.92rem] font-semibold">RevShieldra AI Flow</p>
                <p class="text-xs text-slate-300">Smart multi-language support</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button id="chatbot-restart" class="rounded-2xl border border-slate-500/30 bg-white/10 px-3 py-1 text-[11px] text-slate-200 transition hover:bg-white/10">{{ app()->getLocale() === 'ar' ? 'إعادة المحادثة' : 'Restart' }}</button>
            <button id="chatbot-close" class="text-slate-300 transition hover:text-white">✕</button>
        </div>
    </div>

    <div id="chatbot-body" class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50 text-sm"></div>

    <div id="flow-lang-select" class="space-y-4 border-t border-slate-200 bg-white p-4 text-center">
        <p class="text-sm font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'اختر لغتك للبدء' : 'Choose your language to start' }}</p>
        <div class="grid gap-3 sm:grid-cols-2">
            <button type="button" onclick="setChatLanguage('ar')" class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">العربية 🇸🇦</button>
            <button type="button" onclick="setChatLanguage('en')" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 transition hover:border-slate-300">English 🇬🇧</button>
        </div>
    </div>

    <div id="flow-ticket-form" class="hidden space-y-3 border-t border-red-100 bg-red-50 p-4 text-sm text-slate-800">
        <div class="space-y-2">
            <p class="font-semibold text-red-900">📬 {{ app()->getLocale() === 'ar' ? 'طلب الدعم الفني' : 'Support ticket request' }}</p>
            <p class="text-xs text-red-700">{{ app()->getLocale() === 'ar' ? 'إذا لم أتمكن من مساعدتك، أرسل المشكلة وسيتلقى فريقنا إشعاراً فوريًا.' : 'If the bot cannot help, send your details and our team will receive an instant notification.' }}</p>
        </div>
        <input id="ticket-category" type="hidden" value="" />
        <input id="ticket-name" type="text" value="" placeholder="{{ app()->getLocale() === 'ar' ? 'الاسم (اختياري)' : 'Name (optional)' }}" class="w-full rounded-2xl border border-red-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-red-400" />
        <input id="ticket-email" type="email" value="{{ auth()->check() ? auth()->user()->email : '' }}" placeholder="{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}" class="w-full rounded-2xl border border-red-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-red-400" />
        <textarea id="ticket-issue" rows="3" placeholder="{{ app()->getLocale() === 'ar' ? 'اكتب مشكلتك أو سؤالك هنا...' : 'Describe your issue or question...' }}" class="w-full resize-none rounded-2xl border border-red-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-red-400"></textarea>
        <button type="button" onclick="submitSupportTicket()" class="w-full rounded-2xl bg-red-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-700">{{ app()->getLocale() === 'ar' ? 'إرسال التذكرة' : 'Send ticket' }}</button>
    </div>

    <div id="quick-replies" class="hidden border-t border-slate-200 bg-slate-100 p-4 text-sm space-y-2 overflow-y-auto max-h-32"></div>

    <div id="chatbot-footer" class="hidden items-center gap-2 border-t border-slate-200 bg-white p-3">
        <input id="chatbot-input" type="text" placeholder="{{ app()->getLocale() === 'ar' ? 'اكتب سؤالك هنا...' : 'Type your question...' }}" class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-slate-400" />
        <button id="chatbot-send-btn" type="button" class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">{{ app()->getLocale() === 'ar' ? 'إرسال' : 'Send' }}</button>
    </div>
</div>

<script>
    const csrfToken = @json(csrf_token());
    const chatbotEndpoint = @json(route('chatbot.process', [], false));
    const sessionId = localStorage.getItem('chat_session_id') || setSessionId();
    let currentLang = 'ar';
    let botReady = false;
    let currentFlowContext = { flow: null, branch: null };

    document.getElementById('chatbot-toggle').addEventListener('click', () => {
        document.getElementById('chatbot-box').classList.toggle('hidden');
        if (!botReady) {
            showIntro();
        }
    });

    document.getElementById('chatbot-close').addEventListener('click', () => {
        document.getElementById('chatbot-box').classList.add('hidden');
    });

    document.getElementById('chatbot-restart').addEventListener('click', resetChatFlow);
    document.getElementById('chatbot-send-btn').addEventListener('click', sendUserMessage);
    document.getElementById('chatbot-input').addEventListener('keypress', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            sendUserMessage();
        }
    });

    function setSessionId() {
        const id = 'session_' + Math.random().toString(36).substring(2, 11);
        localStorage.setItem('chat_session_id', id);
        return id;
    }

    function showIntro() {
        botReady = true;
        document.getElementById('flow-lang-select').classList.remove('hidden');
        document.getElementById('chatbot-footer').classList.add('hidden');
        document.getElementById('flow-ticket-form').classList.add('hidden');
        document.getElementById('quick-replies').classList.add('hidden');
        const messages = document.getElementById('chat-messages-container');
        if (messages) {
            messages.remove();
        }

        const emptyHolder = document.createElement('div');
        emptyHolder.id = 'chat-messages-container';
        emptyHolder.className = 'space-y-3 overflow-y-auto p-0';
        document.getElementById('chatbot-body').prepend(emptyHolder);
    }

    function resetChatFlow() {
        appendMessage(currentLang === 'ar' 
            ? '🔄 تم إعادة تشغيل المحادثة. سيتم فتح القائمة الرئيسية من جديد...'
            : '🔄 Chat restarted. Reopening main menu...',
            'bot'
        );
        currentFlowContext = { flow: null, branch: null };
        setTimeout(() => {
            showRootMenu();
        }, 800);
    }

    function setChatLanguage(lang) {
        currentLang = lang;
        document.getElementById('flow-lang-select').classList.add('hidden');
        document.getElementById('chatbot-footer').classList.add('hidden');
        document.getElementById('quick-replies').classList.remove('hidden');
        
        appendMessage(
            lang === 'ar'
                ? '👋 أهلاً بك في RevShieldra! أنا مساعدك الذكي لإدارة السمعة الرقمية. اختر أحد المسارات التالية:'
                : '👋 Welcome to RevShieldra! I\'m your AI assistant. Select a path below:',
            'bot'
        );
        
        showRootMenu();
        document.getElementById('widget-trigger-text').innerText = lang === 'ar' ? 'المساعد الذكي' : 'Smart Assistant';
    }

    function showRootMenu() {
        fetch(chatbotEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                session_id: sessionId,
                action: 'flow',
                language: currentLang,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.show_menu) {
                const buttons = [];
                for (const [key, item] of Object.entries(data.show_menu)) {
                    buttons.push({
                        key: key,
                        label: item.label,
                        type: 'flow'
                    });
                }
                showFlowMenu(buttons);
            }
        })
        .catch(error => console.error('Error fetching root menu:', error));
    }

    function showFlowMenu(buttons) {
        const container = document.getElementById('quick-replies');
        container.innerHTML = '';
        container.classList.remove('hidden');

        buttons.forEach((btn) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-full text-left rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 hover:border-slate-400';
            button.textContent = btn.label;
            button.addEventListener('click', () => {
                navigateToFlow(btn.key);
            });
            container.appendChild(button);
        });

        scrollChatToBottom();
    }

    function navigateToFlow(flowKey) {
        currentFlowContext.flow = flowKey;

        fetch(chatbotEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                session_id: sessionId,
                action: 'navigate',
                language: currentLang,
                flow_key: flowKey,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.branches) {
                appendMessage(
                    currentLang === 'ar' ? '📂 اختر أحد الخيارات التالية:' : '📂 Select an option below:',
                    'bot'
                );
                
                const buttons = [];
                for (const [key, branch] of Object.entries(data.branches)) {
                    buttons.push({
                        key: key,
                        label: branch.label,
                        flowKey: flowKey
                    });
                }
                showBranchMenu(buttons, flowKey);
            }
        })
        .catch(error => console.error('Error fetching branches:', error));
    }

    function showBranchMenu(branches, flowKey) {
        const container = document.getElementById('quick-replies');
        container.innerHTML = '';
        container.classList.remove('hidden');

        branches.forEach((branch) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-full text-left rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 hover:border-slate-400';
            button.textContent = branch.label;
            button.addEventListener('click', () => {
                selectBranch(flowKey, branch.key);
            });
            container.appendChild(button);
        });

        // Add back button
        const backButton = document.createElement('button');
        backButton.type = 'button';
        backButton.className = 'w-full text-left rounded-2xl border border-dashed border-slate-300 bg-slate-100 px-4 py-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-200';
        backButton.textContent = currentLang === 'ar' ? '⬅️ العودة للقائمة الرئيسية' : '⬅️ Back to Main Menu';
        backButton.addEventListener('click', () => {
            appendMessage(
                currentLang === 'ar' ? '⬅️ تم الرجوع للقائمة الرئيسية' : '⬅️ Returned to Main Menu',
                'bot'
            );
            showRootMenu();
        });
        container.appendChild(backButton);

        scrollChatToBottom();
    }

    function selectBranch(flowKey, branchKey) {
        currentFlowContext.flow = flowKey;
        currentFlowContext.branch = branchKey;

        fetch(chatbotEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                session_id: sessionId,
                action: 'navigate',
                language: currentLang,
                flow_key: flowKey,
                branch_key: branchKey,
            }),
        })
        .then(response => response.json())
        .then(data => {
            clearQuickReplies();
            appendMessage(data.reply, 'bot');
            
            if (data.category) {
                document.getElementById('ticket-category').value = data.category;
            }

            if (data.sub_options) {
                showSubOptions(data.sub_options, flowKey, branchKey, data.category);
            }
        })
        .catch(error => console.error('Error selecting branch:', error));
    }

    function showSubOptions(subOptions, flowKey, branchKey, category) {
        const container = document.getElementById('quick-replies');
        container.innerHTML = '';
        container.classList.remove('hidden');

        for (const [key, option] of Object.entries(subOptions)) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = key === 'support' 
                ? 'w-full text-left rounded-2xl border border-red-300 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 transition hover:bg-red-100'
                : 'w-full text-left rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50';
            
            button.textContent = option.label;
            
            if (key === 'support') {
                button.addEventListener('click', () => {
                    openSupportTicket(category, flowKey, branchKey);
                });
            } else if (key === 'back') {
                button.addEventListener('click', () => {
                    appendMessage(
                        currentLang === 'ar' ? '⬅️ تم الرجوع للقائمة السابقة' : '⬅️ Returned to previous menu',
                        'bot'
                    );
                    navigateToFlow(flowKey);
                });
            }
            
            container.appendChild(button);
        }

        scrollChatToBottom();
    }

    function openSupportTicket(category, flowKey, branchKey) {
        clearQuickReplies();
        document.getElementById('flow-ticket-form').classList.remove('hidden');
        document.getElementById('ticket-category').value = category || 'general';
        
        appendMessage(
            currentLang === 'ar' 
                ? '📬 يمكنك الآن إرسال طلب دعم تفصيلي. سيصل طلبك مباشرة إلى فريق الدعم الفني:'
                : '📬 You can now send a detailed support request. It will reach our support team:',
            'bot'
        );
        
        document.getElementById('ticket-issue').focus();
        scrollChatToBottom();
    }

    function clearQuickReplies() {
        const container = document.getElementById('quick-replies');
        container.innerHTML = '';
        container.classList.add('hidden');
    }

    function sendUserMessage() {
        const input = document.getElementById('chatbot-input');
        const message = input.value.trim();
        if (!message) return;

        appendMessage(message, 'user');
        input.value = '';
        clearQuickReplies();
        document.getElementById('flow-ticket-form').classList.add('hidden');

        fetch(chatbotEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                session_id: sessionId,
                action: 'chat',
                language: currentLang,
                message: message,
            }),
        })
        .then(response => response.json())
        .then(data => {
            appendMessage(data.reply || (currentLang === 'ar' ? 'حدث خطأ، حاول لاحقاً.' : 'Something went wrong, please try again.'), 'bot');
            
            if (data.show_root_menu) {
                appendMessage(
                    currentLang === 'ar' ? '📂 إليك القائمة الرئيسية مرة أخرى:' : '📂 Here\'s the main menu again:',
                    'bot'
                );
                showRootMenu();
            } else if (data.fallback) {
                appendMessage(
                    currentLang === 'ar' 
                        ? 'إذا احتجت مساعدة إضافية، يمكنك التواصل مع الدعم الفني:'
                        : 'If you need additional help, you can contact human support:',
                    'bot'
                );
                document.getElementById('flow-ticket-form').classList.remove('hidden');
                showMainMenuReturn();
            } else {
                showMainMenuReturn();
            }
        })
        .catch(error => {
            appendMessage(error?.message || (currentLang === 'ar' ? 'حدث خطأ في الاتصال.' : 'Connection error.'), 'bot');
        });
    }

    function submitSupportTicket() {
        const email = document.getElementById('ticket-email').value.trim();
        const issue = document.getElementById('ticket-issue').value.trim();
        const name = document.getElementById('ticket-name').value.trim();
        const category = document.getElementById('ticket-category').value || 'general';

        if (!email || !issue) {
            alert(currentLang === 'ar' ? 'الرجاء ملء البريد والمشكلة.' : 'Please fill in email and issue.');
            return;
        }

        fetch(chatbotEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                session_id: sessionId,
                action: 'ticket',
                language: currentLang,
                name: name,
                email: email,
                message: issue,
                category: category,
            }),
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('flow-ticket-form').classList.add('hidden');
            clearQuickReplies();
            appendMessage(data.reply || (currentLang === 'ar' ? 'تم إرسال التذكرة.' : 'Ticket sent.'), 'bot');
            
            appendMessage(
                currentLang === 'ar' 
                    ? '✅ شكراً لتواصلك معنا! سيرد عليك فريق الدعم قريباً على بريدك الإلكتروني.'
                    : '✅ Thank you for contacting us! Our support team will reply to your email soon.',
                'bot'
            );
            
            setTimeout(() => {
                resetChatFlow();
            }, 2000);
        })
        .catch(error => {
            appendMessage(error?.message || (currentLang === 'ar' ? 'فشل إرسال التذكرة.' : 'Ticket submission failed.'), 'bot');
        });
    }

    function showMainMenuReturn() {
        const container = document.getElementById('quick-replies');
        container.innerHTML = '';

        const menuButton = document.createElement('button');
        menuButton.type = 'button';
        menuButton.className = 'w-full text-left rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50';
        menuButton.textContent = currentLang === 'ar' ? '🏠 العودة للقائمة الرئيسية' : '🏠 Return to Main Menu';
        menuButton.addEventListener('click', () => {
            appendMessage(
                currentLang === 'ar' ? '🏠 تم فتح القائمة الرئيسية من جديد' : '🏠 Main menu reopened',
                'bot'
            );
            showRootMenu();
        });
        container.appendChild(menuButton);

        container.classList.remove('hidden');
        scrollChatToBottom();
    }

    function appendMessage(text, sender) {
        let messages = document.getElementById('chat-messages-container');
        if (!messages) {
            messages = document.createElement('div');
            messages.id = 'chat-messages-container';
            messages.className = 'space-y-3 overflow-y-auto p-0';
            document.getElementById('chatbot-body').prepend(messages);
        }

        const msgDiv = document.createElement('div');
        msgDiv.className = sender === 'bot' 
            ? 'rounded-2xl bg-white p-3 text-slate-800 border border-slate-200'
            : 'ml-auto rounded-2xl bg-slate-900 p-3 text-white max-w-xs';

        msgDiv.textContent = text;
        messages.appendChild(msgDiv);
        scrollChatToBottom();
    }

    function scrollChatToBottom() {
        const body = document.getElementById('chatbot-body');
        setTimeout(() => {
            body.scrollTop = body.scrollHeight;
        }, 0);
    }

    // Initialize
    showIntro();
</script>
