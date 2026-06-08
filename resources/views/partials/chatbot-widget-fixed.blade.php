<div id="chatbot-toggle" class="fixed bottom-5 right-5 z-50 flex items-center gap-2 rounded-full bg-slate-900 px-4 py-3 text-white shadow-2xl transition duration-200 hover:scale-105 cursor-pointer">
    <span class="text-xl">💬</span>
    <span id="widget-trigger-text" class="font-semibold text-sm">{{ app()->getLocale() === 'ar' ? 'المساعد الذكي' : 'Smart Assistant' }}</span>
</div>

<div id="chatbot-box" class="hidden fixed bottom-28 right-5 z-50 flex h-[520px] w-[92vw] max-w-xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl md:w-[420px]">
    <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-950 px-4 py-4 text-white">
        <div class="flex items-center gap-3">
            <span class="flex h-3 w-3 rounded-full bg-emerald-400 animate-pulse"></span>
            <div>
                <p class="text-[0.92rem] font-semibold">RevShieldra AI</p>
                <p class="text-xs text-slate-300">Choose or ask anything</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button id="chatbot-restart" class="rounded-2xl border border-slate-500/30 bg-white/10 px-3 py-1 text-[11px] text-slate-200 transition hover:bg-white/10">{{ app()->getLocale() === 'ar' ? 'إعادة' : 'Restart' }}</button>
            <button id="chatbot-close" class="text-slate-300 transition hover:text-white">✕</button>
        </div>
    </div>

    <div id="chatbot-body" class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50 text-sm"></div>

    <div id="flow-lang-select" class="space-y-4 border-t border-slate-200 bg-white p-4 text-center">
        <p class="text-sm font-semibold text-slate-700">{{ app()->getLocale() === 'ar' ? 'اختر لغتك' : 'Choose language' }}</p>
        <div class="grid gap-3 sm:grid-cols-2">
            <button type="button" onclick="setChatLanguage('ar')" class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">العربية 🇸🇦</button>
            <button type="button" onclick="setChatLanguage('en')" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 transition hover:border-slate-300">English 🇬🇧</button>
        </div>
    </div>

    <div id="quick-replies" class="hidden border-t border-slate-200 bg-slate-100 p-4 text-sm space-y-2 overflow-y-auto max-h-40"></div>

    <div id="flow-ticket-form" class="hidden space-y-3 border-t border-red-100 bg-red-50 p-4 text-sm text-slate-800">
        <div class="space-y-2">
            <p class="font-semibold text-red-900">📬 {{ app()->getLocale() === 'ar' ? 'طلب دعم' : 'Request Support' }}</p>
        </div>
        <input id="ticket-category" type="hidden" value="general" />
        <input id="ticket-name" type="text" placeholder="{{ app()->getLocale() === 'ar' ? 'الاسم (اختياري)' : 'Name (optional)' }}" class="w-full rounded-2xl border border-red-200 bg-white px-3 py-2 text-sm outline-none focus:border-red-400" />
        <input id="ticket-email" type="email" placeholder="{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}" class="w-full rounded-2xl border border-red-200 bg-white px-3 py-2 text-sm outline-none focus:border-red-400" />
        <textarea id="ticket-issue" rows="3" placeholder="{{ app()->getLocale() === 'ar' ? 'المشكلة...' : 'Describe issue...' }}" class="w-full resize-none rounded-2xl border border-red-200 bg-white px-3 py-2 text-sm outline-none focus:border-red-400"></textarea>
        <button type="button" onclick="submitSupportTicket()" class="w-full rounded-2xl bg-red-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-700">{{ app()->getLocale() === 'ar' ? 'إرسال' : 'Send' }}</button>
    </div>

    <div id="chatbot-footer" class="hidden items-center gap-2 border-t border-slate-200 bg-white p-3">
        <input id="chatbot-input" type="text" placeholder="{{ app()->getLocale() === 'ar' ? 'اسأل أو اكتب سؤالك...' : 'Ask or type your question...' }}" class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-slate-400" />
        <button id="chatbot-send-btn" type="button" class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">{{ app()->getLocale() === 'ar' ? 'إرسال' : 'Send' }}</button>
    </div>
</div>

<script>
    let currentLang = null;
    let sessionId = null;
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const chatbotEndpoint = @json(route('chatbot.process', [], false));
    let inFlowMenu = false;
    let currentFlowContext = { flow: null, branch: null, category: null };

    // Initialize
    function initChatbot() {
        sessionId = 'session_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('chatbot_session', sessionId);

        document.getElementById('chatbot-toggle').addEventListener('click', () => {
            document.getElementById('chatbot-box').classList.toggle('hidden');
        });

        document.getElementById('chatbot-close').addEventListener('click', () => {
            document.getElementById('chatbot-box').classList.add('hidden');
        });

        document.getElementById('chatbot-restart').addEventListener('click', resetChatFlow);
        document.getElementById('chatbot-send-btn').addEventListener('click', sendUserMessage);
        document.getElementById('chatbot-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendUserMessage();
        });
    }

    function sendUserMessage() {
        const input = document.getElementById('chatbot-input');
        const message = input.value.trim();
        if (!message) return;

        appendMessage(message, 'user');
        input.value = '';

        // If in flow menu, treat as free-form chat search
        if (inFlowMenu) {
            searchFlowsForAnswer(message);
            return;
        }

        // Otherwise, send as regular chat
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
            appendMessage(data.reply, 'bot');
            if (data.flow === 'ticket_prompt' || data.fallback) {
                document.getElementById('chatbot-footer').classList.add('hidden');
                document.getElementById('flow-ticket-form').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Chat error:', error);
            appendMessage(currentLang === 'ar' ? 'عذراً، حدث خطأ.' : 'Sorry, an error occurred.', 'bot');
        });
    }

    function searchFlowsForAnswer(query) {
        // Try to find matching flow from user's free-form message
        const keyword = query.toLowerCase();
        const flows = getCachedFlows(currentLang);
        
        if (!flows) {
            appendMessage(currentLang === 'ar' ? 'اختر من الخيارات أعلاه' : 'Please select from options above', 'bot');
            return;
        }

        // Search in flows and branches
        for (const [flowKey, flowData] of Object.entries(flows['root']['flows'])) {
            if (flowData.label.toLowerCase().includes(keyword)) {
                appendMessage(flowData.label, 'bot');
                navigateToFlow(flowKey);
                return;
            }
            // Search in branches
            for (const [branchKey, branchData] of Object.entries(flowData.branches)) {
                if (branchData.label.toLowerCase().includes(keyword) || 
                    branchData.response.toLowerCase().includes(keyword)) {
                    selectBranch(flowKey, branchKey);
                    return;
                }
            }
        }

        // If no match, offer support
        appendMessage(
            currentLang === 'ar' 
                ? 'لم أجد إجابة مطابقة. هل تريد التواصل مع فريق الدعم؟' 
                : 'No matching answer found. Would you like to contact support?', 
            'bot'
        );
        document.getElementById('chatbot-footer').classList.add('hidden');
        document.getElementById('flow-ticket-form').classList.remove('hidden');
    }

    function setChatLanguage(lang) {
        currentLang = lang;
        inFlowMenu = true;
        document.getElementById('flow-lang-select').classList.add('hidden');
        document.getElementById('chatbot-footer').classList.remove('hidden');
        document.getElementById('quick-replies').classList.remove('hidden');
        
        appendMessage(
            lang === 'ar'
                ? '👋 أهلاً! اختر من الخيارات أو اكتب سؤالك مباشرة'
                : '👋 Welcome! Select an option or type your question',
            'bot'
        );
        
        showRootMenu();
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
            cachFlows(currentLang, data);
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
        .catch(error => console.error('Root menu error:', error));
    }

    function showFlowMenu(buttons) {
        const container = document.getElementById('quick-replies');
        container.innerHTML = '';
        container.classList.remove('hidden');

        buttons.forEach((btn) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-full text-left rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50';
            button.textContent = btn.label;
            button.addEventListener('click', () => navigateToFlow(btn.key));
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
                appendMessage(currentLang === 'ar' ? '📂 اختر:' : '📂 Select:', 'bot');
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
        .catch(error => console.error('Navigate error:', error));
    }

    function showBranchMenu(branches, flowKey) {
        const container = document.getElementById('quick-replies');
        container.innerHTML = '';

        branches.forEach((branch) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-full text-left rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50';
            button.textContent = branch.label;
            button.addEventListener('click', () => selectBranch(flowKey, branch.key));
            container.appendChild(button);
        });

        // Back button
        const backButton = document.createElement('button');
        backButton.type = 'button';
        backButton.className = 'w-full text-left rounded-2xl border border-dashed border-slate-300 bg-slate-100 px-4 py-3 text-xs font-semibold text-slate-600 hover:bg-slate-200';
        backButton.textContent = currentLang === 'ar' ? '⬅️ العودة' : '⬅️ Back';
        backButton.addEventListener('click', () => {
            appendMessage(currentLang === 'ar' ? '⬅️ تم الرجوع' : '⬅️ Returned', 'bot');
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
            document.getElementById('quick-replies').innerHTML = '';
            appendMessage(data.reply, 'bot');
            
            if (data.category) {
                currentFlowContext.category = data.category;
                document.getElementById('ticket-category').value = data.category;
            }

            if (data.sub_options) {
                showSubOptions(data.sub_options, flowKey, branchKey);
            }
        })
        .catch(error => console.error('Select branch error:', error));
    }

    function showSubOptions(subOptions, flowKey, branchKey) {
        const container = document.getElementById('quick-replies');
        container.innerHTML = '';
        container.classList.remove('hidden');

        for (const [key, option] of Object.entries(subOptions)) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = key === 'support' 
                ? 'w-full text-left rounded-2xl border border-red-300 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 hover:bg-red-100'
                : 'w-full text-left rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50';
            
            button.textContent = option.label;
            
            if (key === 'support') {
                button.addEventListener('click', () => {
                    document.getElementById('quick-replies').innerHTML = '';
                    document.getElementById('quick-replies').classList.add('hidden');
                    document.getElementById('chatbot-footer').classList.add('hidden');
                    document.getElementById('flow-ticket-form').classList.remove('hidden');
                });
            } else {
                button.addEventListener('click', () => {
                    appendMessage(option.label, 'bot');
                    showRootMenu();
                });
            }
            container.appendChild(button);
        }
        scrollChatToBottom();
    }

    function submitSupportTicket() {
        const email = document.getElementById('ticket-email').value;
        const message = document.getElementById('ticket-issue').value;
        const category = currentFlowContext.category || 'general';

        if (!email || !message) {
            alert(currentLang === 'ar' ? 'يرجى ملء البريد والرسالة' : 'Please fill email and message');
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
                name: document.getElementById('ticket-name').value,
                email: email,
                message: message,
                category: category,
            }),
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('flow-ticket-form').classList.add('hidden');
            document.getElementById('chatbot-footer').classList.remove('hidden');
            appendMessage(data.reply, 'bot');
        })
        .catch(error => console.error('Ticket error:', error));
    }

    function resetChatFlow() {
        appendMessage(currentLang === 'ar' ? '🔄 جاري إعادة تشغيل...' : '🔄 Restarting...', 'bot');
        currentFlowContext = { flow: null, branch: null };
        document.getElementById('chatbot-body').innerHTML = '';
        document.getElementById('quick-replies').innerHTML = '';
        document.getElementById('quick-replies').classList.add('hidden');
        document.getElementById('flow-ticket-form').classList.add('hidden');
        document.getElementById('chatbot-footer').classList.add('hidden');
        document.getElementById('flow-lang-select').classList.remove('hidden');
        inFlowMenu = false;
    }

    function appendMessage(text, sender) {
        const chatBody = document.getElementById('chatbot-body');
        const msgDiv = document.createElement('div');
        msgDiv.className = sender === 'user' 
            ? 'ml-auto w-4/5 rounded-2xl bg-slate-900 px-4 py-3 text-white text-sm'
            : 'mr-auto w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-800 text-sm';
        msgDiv.textContent = text;
        chatBody.appendChild(msgDiv);
        scrollChatToBottom();
    }

    function scrollChatToBottom() {
        const chatBody = document.getElementById('chatbot-body');
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function cachFlows(lang, data) {
        localStorage.setItem(`chatbot_flows_${lang}`, JSON.stringify(data));
    }

    function getCachedFlows(lang) {
        try {
            return JSON.parse(localStorage.getItem(`chatbot_flows_${lang}`));
        } catch {
            return null;
        }
    }

    initChatbot();
</script>
