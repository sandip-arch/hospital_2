@auth
<!-- ========================================================================= -->
<!-- 🏥 APEX HORIZON AUTONOMOUS AI ASSISTANT - FLOATING CHAT WIDGET             -->
<!-- ========================================================================= -->
<div id="apex-ai-widget-root" class="fixed bottom-6 right-6 z-50 font-sans text-slate-800 antialiased" style="isolation: isolate;">
    
    <!-- Floating Trigger Launcher Button -->
    <button id="apex-ai-launcher" 
            onclick="apexAiToggleWidget()"
            aria-label="Open Hospital AI Assistant"
            class="group relative flex items-center gap-3 bg-gradient-to-r from-indigo-600 via-blue-600 to-cyan-500 hover:from-indigo-700 hover:to-cyan-600 text-white px-4 py-3.5 rounded-full shadow-2xl transition-all duration-300 transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-indigo-300">
        
        <!-- Pulsing aura ring -->
        <span class="absolute -inset-1 rounded-full bg-gradient-to-r from-indigo-500 to-cyan-400 opacity-60 blur-sm group-hover:opacity-90 animate-pulse transition duration-1000"></span>
        
        <div class="relative flex items-center justify-center w-8 h-8 rounded-full bg-white/20 backdrop-blur-md">
            <i class="fa-solid fa-sparkles text-amber-300 text-sm group-hover:rotate-12 transition-transform duration-300"></i>
        </div>

        <div class="relative flex flex-col text-left">
            <span class="text-xs font-black uppercase tracking-widest text-cyan-100 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                Apex AI
            </span>
            <span class="text-[11px] font-semibold text-white/90">Hospital Assistant</span>
        </div>

        <div class="relative hidden sm:flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/20 border border-white/30 text-white backdrop-blur-md">
            {{ Auth::user()->primaryRoleDisplay() }}
        </div>
    </button>

    <!-- Expanding Chat Window Modal -->
    <div id="apex-ai-window" 
         class="hidden fixed bottom-6 right-6 w-[94vw] sm:w-[460px] h-[650px] max-h-[88vh] bg-white rounded-3xl shadow-2xl border border-slate-200/80 flex flex-col overflow-hidden backdrop-blur-xl transition-all duration-300 animate-in fade-in slide-in-from-bottom-5">
        
        <!-- Window Header -->
        <div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-4 sm:p-5 flex items-center justify-between border-b border-indigo-900/50 shadow-md">
            <div class="flex items-center gap-3">
                <div class="relative w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-500 to-cyan-400 flex items-center justify-center shadow-inner">
                    <i class="fa-solid fa-user-doctor text-white text-lg"></i>
                    <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 border-2 border-slate-900 rounded-full"></span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-white tracking-wide">Apex Horizon AI</h3>
                        <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-indigo-500/30 text-cyan-300 border border-indigo-400/30">
                            {{ Auth::user()->primaryRoleDisplay() }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-300 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Autonomous RAG & Clinical Tools
                    </p>
                </div>
            </div>

            <!-- Header Controls -->
            <div class="flex items-center gap-1">
                <!-- Clear History Button -->
                <button onclick="apexAiClearChat()" title="Reset Conversation" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center justify-center text-xs transition">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
                <!-- Minimize Button -->
                <button onclick="apexAiToggleWidget()" title="Minimize" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center justify-center text-xs transition">
                    <i class="fa-solid fa-minus"></i>
                </button>
            </div>
        </div>

        <!-- Emergency Quick Banner (For instant crisis access) -->
        <div class="bg-rose-50 border-b border-rose-100 px-4 py-2 flex items-center justify-between text-xs text-rose-800">
            <div class="flex items-center gap-2 font-medium">
                <i class="fa-solid fa-phone-volume text-rose-600 animate-bounce"></i>
                <span>Emergency Hotline: <strong>+1 (800) 555-APEX / 911</strong></span>
            </div>
            <button onclick="apexAiSendPrompt('Emergency! Please dispatch an ambulance to my location immediately.')" class="bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider transition">
                🚑 Call
            </button>
        </div>

        <!-- Chat Messages Scroll Container -->
        <div id="apex-ai-messages" class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50/70 text-xs custom-scrollbar">
            
            <!-- Greeting Bubble -->
            <div class="flex items-start gap-2.5">
                <div class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm text-[11px]">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
                <div class="max-w-[85%] bg-white border border-slate-200/90 rounded-2xl rounded-tl-sm p-3.5 shadow-sm space-y-2 text-slate-700 leading-relaxed">
                    <p class="font-bold text-slate-900">
                        Hello {{ Auth::user()->name }}! 👋
                    </p>
                    <p>
                        I am your hospital AI specialist. I can inspect medical records, book appointments, discover available beds, explain medications, and coordinate ambulance emergency dispatches.
                    </p>
                    <p class="text-[10px] text-slate-400 italic">
                        Tip: Click any quick action below or type your question in plain English.
                    </p>
                </div>
            </div>

            <!-- Role Quick-Prompt Chips Container (Auto-populated via AJAX) -->
            <div id="apex-ai-chips-wrapper" class="space-y-1.5 pt-2">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">Suggested Clinical Actions</p>
                <div id="apex-ai-chips" class="flex flex-wrap gap-1.5">
                    <!-- Populated dynamically -->
                </div>
            </div>

        </div>

        <!-- Typing / Tool Execution Loading Indicator -->
        <div id="apex-ai-typing" class="hidden px-4 py-2 bg-slate-50/80 border-t border-slate-100 flex items-center gap-2 text-xs text-indigo-700 font-medium">
            <div class="flex gap-1 items-center">
                <span class="w-2 h-2 rounded-full bg-indigo-600 animate-bounce" style="animation-delay: 0ms;"></span>
                <span class="w-2 h-2 rounded-full bg-indigo-600 animate-bounce" style="animation-delay: 150ms;"></span>
                <span class="w-2 h-2 rounded-full bg-indigo-600 animate-bounce" style="animation-delay: 300ms;"></span>
            </div>
            <span id="apex-ai-typing-text">AI reasoning & analyzing medical database...</span>
        </div>

        <!-- Input Bar & Submission Form -->
        <div class="p-3 bg-white border-t border-slate-200/90">
            <form id="apex-ai-form" onsubmit="apexAiSubmitMessage(event)" class="relative flex items-center gap-2">
                <input type="text" 
                       id="apex-ai-input" 
                       placeholder="Ask about appointments, medications, beds, ambulances..."
                       autocomplete="off"
                       class="w-full bg-slate-100 hover:bg-slate-100/80 focus:bg-white text-xs text-slate-800 placeholder-slate-400 px-4 py-3 rounded-2xl border border-transparent focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition" />
                
                <button type="submit" 
                        id="apex-ai-send-btn"
                        title="Send message"
                        class="w-10 h-10 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center shrink-0 shadow-md transition transform active:scale-95 disabled:opacity-50">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                </button>
            </form>
            <div class="mt-1.5 flex items-center justify-between text-[10px] text-slate-400 px-2">
                <span>Press <kbd class="px-1 py-0.5 rounded bg-slate-100 border text-slate-500">Enter</kbd> to submit</span>
                <span>Role: <strong class="text-indigo-600">{{ Auth::user()->primaryRoleDisplay() }}</strong></span>
            </div>
        </div>

    </div>
</div>

<!-- Widget Client Script -->
<script>
    let apexAiConversationId = null;
    let apexAiIsOpen = false;

    // Toggle widget modal visibility
    function apexAiToggleWidget() {
        const win = document.getElementById('apex-ai-window');
        const launcher = document.getElementById('apex-ai-launcher');
        
        apexAiIsOpen = !apexAiIsOpen;
        if (apexAiIsOpen) {
            win.classList.remove('hidden');
            launcher.classList.add('hidden');
            document.getElementById('apex-ai-input').focus();
            apexAiLoadChips();
        } else {
            win.classList.add('hidden');
            launcher.classList.remove('hidden');
        }
    }

    // Load role-aware prompt suggestions
    async function apexAiLoadChips() {
        const container = document.getElementById('apex-ai-chips');
        if (container.children.length > 0) return;

        try {
            const res = await fetch("{{ route('ai.prompts') }}", {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            
            if (data.status === 'success' && data.prompts) {
                container.innerHTML = '';
                data.prompts.forEach(p => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-indigo-50 hover:bg-indigo-100 border border-indigo-200/80 text-[11px] font-semibold text-indigo-700 transition transform hover:scale-[1.02] active:scale-95';
                    btn.innerHTML = `<i class="fa-solid ${p.icon} text-indigo-600 text-[10px]"></i> ${p.label}`;
                    btn.onclick = () => apexAiSendPrompt(p.prompt);
                    container.appendChild(btn);
                });
            }
        } catch (e) {
            console.warn('Could not load AI prompt chips:', e);
        }
    }

    // Send predefined prompt from chips or emergency button
    function apexAiSendPrompt(text) {
        document.getElementById('apex-ai-input').value = text;
        apexAiSubmitMessage(new Event('submit'));
    }

    // Submit user message via AJAX to /ai/chat
    async function apexAiSubmitMessage(e) {
        if (e && e.preventDefault) e.preventDefault();
        
        const input = document.getElementById('apex-ai-input');
        const text = input.value.trim();
        if (!text) return;

        // Append user bubble
        apexAiAppendBubble('user', text);
        input.value = '';

        // Show typing indicator
        const typing = document.getElementById('apex-ai-typing');
        const sendBtn = document.getElementById('apex-ai-send-btn');
        typing.classList.remove('hidden');
        sendBtn.disabled = true;

        try {
            const res = await fetch("{{ route('ai.chat') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    message: text,
                    conversation_id: apexAiConversationId
                })
            });

            const data = await res.json();
            typing.classList.add('hidden');
            sendBtn.disabled = false;

            if (data.conversation_id) {
                apexAiConversationId = data.conversation_id;
            }

            if (data.message) {
                apexAiAppendBubble('model', data.message, data.tool_executed, data.tool_result);
            } else {
                apexAiAppendBubble('model', 'I could not process that request. Please try again or reach the emergency desk.');
            }

        } catch (err) {
            typing.classList.add('hidden');
            sendBtn.disabled = false;
            apexAiAppendBubble('model', 'Network disruption encountered. Please verify your connection or refresh the page.');
            console.error('AI chat error:', err);
        }
    }

    // Reset current conversation
    async function apexAiClearChat() {
        if (!confirm('Clear this AI conversation history?')) return;
        
        try {
            await fetch("{{ route('ai.conversation.clear') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ conversation_id: apexAiConversationId })
            });

            apexAiConversationId = null;
            const container = document.getElementById('apex-ai-messages');
            // Keep initial greeting and chips, remove dynamic bubbles
            const bubbles = container.querySelectorAll('.apex-ai-dynamic-bubble');
            bubbles.forEach(b => b.remove());
        } catch (e) {
            console.error('Clear chat error:', e);
        }
    }

    // Append a message bubble to the chat container
    function apexAiAppendBubble(role, content, toolExecuted, toolResult) {
        const container = document.getElementById('apex-ai-messages');
        const bubble = document.createElement('div');
        bubble.className = 'apex-ai-dynamic-bubble flex items-start gap-2.5';

        if (role === 'user') {
            bubble.classList.add('justify-end');
            bubble.innerHTML = `
                <div class="max-w-[85%] bg-indigo-600 text-white rounded-2xl rounded-tr-sm p-3.5 shadow-sm text-xs leading-relaxed font-medium">
                    ${apexAiEscapeHtml(content)}
                </div>
                <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center shrink-0 shadow-sm text-[11px] font-bold">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
            `;
        } else {
            let toolBadge = '';
            if (toolExecuted) {
                toolBadge = `
                    <div class="mb-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-[10px] font-bold">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                        Action Executed: <code>${apexAiEscapeHtml(toolExecuted)}</code>
                    </div>
                `;
            }

            bubble.innerHTML = `
                <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-indigo-600 to-cyan-500 text-white flex items-center justify-center shrink-0 shadow-sm text-[11px]">
                    <i class="fa-solid fa-sparkles"></i>
                </div>
                <div class="max-w-[88%] bg-white border border-slate-200/90 rounded-2xl rounded-tl-sm p-3.5 shadow-sm text-xs leading-relaxed text-slate-800 space-y-1.5">
                    ${toolBadge}
                    <div class="prose-chat">${apexAiFormatMarkdown(content)}</div>
                </div>
            `;
        }

        container.appendChild(bubble);
        container.scrollTop = container.scrollHeight;
    }

    // Helper: basic markdown formatting (bold, italic, lists, code, blockquotes)
    function apexAiFormatMarkdown(text) {
        if (!text) return '';
        let escaped = apexAiEscapeHtml(text);

        // Bold **text**
        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Italic *text*
        escaped = escaped.replace(/\*(.*?)\*/g, '<em>$1</em>');
        // Inline code `code`
        escaped = escaped.replace(/`([^`]+)`/g, '<code class="bg-slate-100 px-1 py-0.5 rounded text-indigo-600 font-mono text-[10px]">$1</code>');
        // Blockquotes > text
        escaped = escaped.replace(/^>\s*(.*?)$/gm, '<blockquote class="border-l-2 border-indigo-400 pl-2 my-1 text-slate-500 italic">$1</blockquote>');
        // Bullet points • or -
        escaped = escaped.replace(/^[•\-]\s*(.*?)$/gm, '<li class="ml-4 list-disc">$1</li>');
        // Line breaks
        escaped = escaped.replace(/\n/g, '<br/>');

        return escaped;
    }

    // Helper: HTML escape
    function apexAiEscapeHtml(unsafe) {
        return (unsafe || '')
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
@endauth
