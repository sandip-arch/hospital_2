@auth
@unless(request()->routeIs('ai.assistant'))
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
            <div class="flex items-center gap-1.5">
                <!-- Voice Dictation / Speaker Toggle Button -->
                <button type="button" 
                        id="apex-ai-speaker-toggle" 
                        onclick="apexAiToggleSpeaker()" 
                        title="Voice Dictation (Read responses aloud through speaker)" 
                        class="h-8 px-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center gap-1.5 text-xs transition border border-white/10">
                    <i id="apex-ai-speaker-icon" class="fa-solid fa-volume-high text-cyan-300 text-xs"></i>
                    <span id="apex-ai-speaker-label" class="text-[10px] font-bold text-cyan-200 hidden sm:inline">Voice ON</span>
                </button>

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

        <!-- Speaking Status Banner (Active when AI voice is dictating aloud) -->
        <div id="apex-ai-speaking-banner" style="display: none;" class="bg-gradient-to-r from-indigo-900 to-cyan-900 text-cyan-100 px-4 py-1.5 items-center justify-between text-xs border-b border-indigo-700/60 transition-all">
            <div class="flex items-center gap-2">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-cyan-400"></span>
                </span>
                <span class="text-[11px] font-semibold flex items-center gap-1">
                    <i class="fa-solid fa-volume-high text-cyan-300 animate-pulse text-[11px]"></i> Dictating response aloud...
                </span>
            </div>
            <button type="button" onclick="apexAiStopSpeaking()" class="text-[10px] bg-white/15 hover:bg-white/25 text-white px-2 py-0.5 rounded font-bold uppercase tracking-wider transition flex items-center gap-1">
                <i class="fa-solid fa-circle-stop text-rose-400"></i> Stop Voice
            </button>
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
            <!-- Listening / Recording Indicator -->
            <div id="apex-ai-mic-indicator" style="display: none;" class="mb-2 px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs items-center justify-between animate-pulse">
                <span class="flex items-center gap-2 font-semibold text-[11px]">
                    <span class="w-2 h-2 rounded-full bg-rose-600 animate-ping"></span>
                    🎙️ Listening... Speak into your microphone (click Mic again to send)
                </span>
                <button type="button" onclick="apexAiCancelVoiceRecognition()" class="text-[10px] uppercase font-bold text-rose-700 hover:underline">
                    Cancel
                </button>
            </div>

            <form id="apex-ai-form" onsubmit="apexAiSubmitMessage(event)" class="relative flex items-center gap-1.5">
                <input type="text" 
                       id="apex-ai-input" 
                       placeholder="Ask questions or tap mic to talk..."
                       autocomplete="off"
                       class="w-full bg-slate-100 hover:bg-slate-100/80 focus:bg-white text-xs text-slate-800 placeholder-slate-400 pl-4 pr-2 py-3 rounded-2xl border border-transparent focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition" />
                
                <!-- Microphone Button (Voice Input) -->
                <button type="button" 
                        id="apex-ai-mic-btn" 
                        onclick="apexAiToggleVoiceRecognition()" 
                        title="Voice Input (Click to speak into microphone)"
                        class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 flex items-center justify-center shrink-0 border border-slate-200/90 transition transform active:scale-95 shadow-sm">
                    <i id="apex-ai-mic-icon" class="fa-solid fa-microphone text-xs"></i>
                </button>

                <!-- Send Button -->
                <button type="submit" 
                        id="apex-ai-send-btn" 
                        title="Send message"
                        class="w-10 h-10 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center shrink-0 shadow-md transition transform active:scale-95 disabled:opacity-50">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                </button>
            </form>
            <div class="mt-1.5 flex items-center justify-between text-[10px] text-slate-400 px-2">
                <button type="button" onclick="apexAiToggleSpeaker()" class="hover:text-indigo-600 font-semibold cursor-pointer flex items-center gap-1">
                    <i id="apex-ai-footer-spk-icon" class="fa-solid fa-volume-high text-emerald-500 text-[10px]"></i>
                    <span id="apex-ai-voice-status-hint" class="text-indigo-600 font-bold">Voice: Active (Speaking)</span>
                </button>
                <span>Role: <strong class="text-indigo-600">{{ Auth::user()->primaryRoleDisplay() }}</strong></span>
            </div>
        </div>

    </div>
</div>

<!-- Widget Client Script -->
<script>
    let apexAiConversationId = null;
    let apexAiIsOpen = false;
    let apexAiRecognition = null;
    let apexAiIsRecording = false;
    let apexAiSpeakerActive = localStorage.getItem('apex_ai_voice_dictation') !== 'false'; // Default to active

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
            apexAiStopSpeaking();
            apexAiStopVoiceRecognition();
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
        
        // Interrupt ongoing speech and stop active recording
        apexAiStopSpeaking();
        apexAiStopVoiceRecognition();

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
                // Dictate generated response aloud through speaker if active
                apexAiSpeak(data.message);
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

    // ==========================================
    // VOICE RECOGNITION (SPEECH-TO-TEXT) LOGIC
    // ==========================================
    function apexAiInitVoice() {
        const SpeechRecClass = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (SpeechRecClass) {
            apexAiRecognition = new SpeechRecClass();
            apexAiRecognition.continuous = false;
            apexAiRecognition.interimResults = true;
            apexAiRecognition.lang = 'en-US';

            apexAiRecognition.onstart = function() {
                apexAiIsRecording = true;
                apexAiUpdateMicUI(true);
            };

            apexAiRecognition.onresult = function(event) {
                let transcript = '';
                for (let i = event.resultIndex; i < event.results.length; ++i) {
                    transcript += event.results[i][0].transcript;
                }
                const input = document.getElementById('apex-ai-input');
                if (input && transcript) {
                    input.value = transcript;
                }
            };

            apexAiRecognition.onerror = function(event) {
                console.warn('Speech recognition error:', event.error);
                apexAiStopVoiceRecognition();
                if (event.error === 'not-allowed') {
                    alert('Microphone permission denied. Please allow microphone access in your browser to talk with AI.');
                }
            };

            apexAiRecognition.onend = function() {
                apexAiIsRecording = false;
                apexAiUpdateMicUI(false);
                const input = document.getElementById('apex-ai-input');
                if (input && input.value.trim().length > 0) {
                    apexAiSubmitMessage(new Event('submit'));
                }
            };
        }

        apexAiUpdateSpeakerUI();
    }

    function apexAiToggleVoiceRecognition() {
        if (!apexAiRecognition) {
            alert('Speech recognition is not supported in this browser. Please use Google Chrome, Edge, or Safari.');
            return;
        }

        apexAiStopSpeaking();

        if (apexAiIsRecording) {
            apexAiStopVoiceRecognition();
        } else {
            try {
                apexAiRecognition.start();
            } catch (err) {
                console.warn('Could not start recognition:', err);
            }
        }
    }

    function apexAiStopVoiceRecognition() {
        if (apexAiRecognition && apexAiIsRecording) {
            try {
                apexAiRecognition.stop();
            } catch (e) {}
        }
        apexAiIsRecording = false;
        apexAiUpdateMicUI(false);
    }

    function apexAiCancelVoiceRecognition() {
        const input = document.getElementById('apex-ai-input');
        if (input) input.value = '';
        apexAiStopVoiceRecognition();
    }

    function apexAiUpdateMicUI(isRecording) {
        const btn = document.getElementById('apex-ai-mic-btn');
        const icon = document.getElementById('apex-ai-mic-icon');
        const indicator = document.getElementById('apex-ai-mic-indicator');

        if (isRecording) {
            if (btn) {
                btn.className = 'w-10 h-10 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center shrink-0 shadow-md transition transform active:scale-95 animate-pulse border border-rose-700';
                btn.title = 'Listening... Click to finish and send';
            }
            if (icon) icon.className = 'fa-solid fa-stop text-xs';
            if (indicator) indicator.style.display = 'flex';
        } else {
            if (btn) {
                btn.className = 'w-10 h-10 rounded-2xl bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 flex items-center justify-center shrink-0 border border-slate-200/90 transition transform active:scale-95 shadow-sm';
                btn.title = 'Voice Input (Click to speak into microphone)';
            }
            if (icon) icon.className = 'fa-solid fa-microphone text-xs';
            if (indicator) indicator.style.display = 'none';
        }
    }

    // ==========================================
    // VOICE DICTATION (SPEAKER / TTS) LOGIC
    // ==========================================
    function apexAiToggleSpeaker() {
        apexAiSpeakerActive = !apexAiSpeakerActive;
        localStorage.setItem('apex_ai_voice_dictation', apexAiSpeakerActive ? 'true' : 'false');
        if (!apexAiSpeakerActive) {
            apexAiStopSpeaking();
        }
        apexAiUpdateSpeakerUI();
    }

    function apexAiUpdateSpeakerUI() {
        const icon = document.getElementById('apex-ai-speaker-icon');
        const label = document.getElementById('apex-ai-speaker-label');
        const footerIcon = document.getElementById('apex-ai-footer-spk-icon');
        const footerHint = document.getElementById('apex-ai-voice-status-hint');
        const btn = document.getElementById('apex-ai-speaker-toggle');

        if (apexAiSpeakerActive) {
            if (icon) icon.className = 'fa-solid fa-volume-high text-cyan-300 text-xs';
            if (label) {
                label.textContent = 'Voice ON';
                label.className = 'text-[10px] font-bold text-cyan-200 hidden sm:inline';
            }
            if (footerIcon) footerIcon.className = 'fa-solid fa-volume-high text-emerald-500 text-[10px]';
            if (footerHint) {
                footerHint.textContent = 'Voice: Active (Speaking)';
                footerHint.className = 'text-indigo-600 font-bold';
            }
            if (btn) btn.title = 'Voice Dictation is Active. Click to Mute.';
        } else {
            if (icon) icon.className = 'fa-solid fa-volume-xmark text-slate-400 text-xs';
            if (label) {
                label.textContent = 'Voice Muted';
                label.className = 'text-[10px] font-bold text-slate-400 hidden sm:inline';
            }
            if (footerIcon) footerIcon.className = 'fa-solid fa-volume-xmark text-slate-400 text-[10px]';
            if (footerHint) {
                footerHint.textContent = 'Voice: Muted (Chat only)';
                footerHint.className = 'text-slate-500 font-bold';
            }
            if (btn) btn.title = 'Voice Dictation is Muted. Click to Turn ON.';
        }
    }

    function apexAiSpeak(rawText) {
        if (!apexAiSpeakerActive || !('speechSynthesis' in window)) return;

        window.speechSynthesis.cancel();

        // Strip markdown and URLs for natural speech
        let speakable = rawText
            .replace(/```[\s\S]*?```/g, '')
            .replace(/`([^`]+)`/g, '$1')
            .replace(/\*\*([^*]+)\*\*/g, '$1')
            .replace(/\*([^*]+)\*/g, '$1')
            .replace(/#+\s*/g, '')
            .replace(/https?:\/\/\S+/g, '')
            .replace(/[•\-\*]\s+/g, ', ')
            .replace(/<[^>]*>/g, '')
            .replace(/\s+/g, ' ')
            .trim();

        if (!speakable) return;

        const utterance = new SpeechSynthesisUtterance(speakable);
        utterance.rate = 1.0;
        utterance.pitch = 1.0;
        utterance.lang = 'en-US';

        const voices = window.speechSynthesis.getVoices();
        if (voices.length > 0) {
            const preferred = voices.find(v => v.lang.startsWith('en') && (v.name.includes('Natural') || v.name.includes('Google') || v.name.includes('Samantha') || v.name.includes('English')));
            if (preferred) utterance.voice = preferred;
        }

        const banner = document.getElementById('apex-ai-speaking-banner');

        utterance.onstart = function() {
            if (banner) banner.style.display = 'flex';
        };

        utterance.onend = function() {
            if (banner) banner.style.display = 'none';
        };

        utterance.onerror = function(e) {
            console.warn('SpeechSynthesis error:', e);
            if (banner) banner.style.display = 'none';
        };

        window.speechSynthesis.speak(utterance);
    }

    function apexAiStopSpeaking() {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
        }
        const banner = document.getElementById('apex-ai-speaking-banner');
        if (banner) banner.style.display = 'none';
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', apexAiInitVoice);
    } else {
        apexAiInitVoice();
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
@endunless
@endauth
