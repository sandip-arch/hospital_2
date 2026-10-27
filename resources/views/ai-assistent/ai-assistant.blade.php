
@extends('layouts.app')

@section('content')

<style>
    .ai-page {
        --ai-primary: #2563eb;
        --ai-primary-dark: #1d4ed8;
        --ai-text: #0f172a;
        --ai-muted: #64748b;
        --ai-border: #e2e8f0;
        color: var(--ai-text);
        font-family: inherit;
    }

    .ai-page * {
        box-sizing: border-box;
    }

    .ai-shell {
        display: grid;
        grid-template-columns: 250px minmax(0, 1fr);
        min-height: 650px;
        height: calc(100vh - 120px);
        max-height: 900px;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--ai-border);
        border-radius: 20px;
        box-shadow: 0 10px 35px rgba(15, 23, 42, 0.06);
    }

    .ai-sidebar {
        display: flex;
        flex-direction: column;
        min-width: 0;
        padding: 22px 16px;
        background: #f8fafc;
        border-right: 1px solid var(--ai-border);
    }

    .ai-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0 5px 24px;
    }

    .ai-brand-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        color: #fff;
        font-size: 24px;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        border-radius: 14px;
    }

    .ai-brand h2 {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
    }

    .ai-brand p {
        margin: 3px 0 0;
        color: var(--ai-muted);
        font-size: 11px;
    }

    .ai-new-chat {
        width: 100%;
        padding: 12px 14px;
        color: #fff;
        font-weight: 700;
        font-size: 13px;
        background: var(--ai-primary);
        border: 0;
        border-radius: 11px;
        cursor: pointer;
        transition: background .2s, transform .2s;
    }

    .ai-new-chat:hover {
        background: var(--ai-primary-dark);
        transform: translateY(-1px);
    }

    .ai-sidebar-heading {
        margin: 28px 8px 12px;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1.2px;
        text-transform: uppercase;
    }

    .ai-sidebar-item {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 11px 10px;
        margin-bottom: 5px;
        color: #475569;
        font-size: 12px;
        text-decoration: none;
        border-radius: 10px;
        transition: background .2s, color .2s;
    }

    .ai-sidebar-item:hover,
    .ai-sidebar-item.active {
        color: #1d4ed8;
        background: #dbeafe;
    }

    .ai-sidebar-item .ai-nav-icon {
        width: 20px;
        text-align: center;
        font-size: 16px;
    }

    .ai-sidebar-footer {
        margin-top: auto;
        padding: 14px;
        background: #fff;
        border: 1px solid var(--ai-border);
        border-radius: 12px;
    }

    .ai-status {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 7px;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
    }

    .ai-status-dot {
        width: 8px;
        height: 8px;
        background: #f59e0b;
        border-radius: 50%;
    }

    .ai-sidebar-footer p {
        margin: 0;
        color: #64748b;
        font-size: 11px;
        line-height: 1.6;
    }

    .ai-main {
        display: flex;
        flex-direction: column;
        min-width: 0;
        min-height: 0;
        background: #fff;
    }

    .ai-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 18px 24px;
        border-bottom: 1px solid #edf2f7;
    }

    .ai-header-title {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .ai-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        color: #fff;
        font-size: 21px;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        border-radius: 14px;
    }

    .ai-header h1 {
        margin: 0;
        color: #0f172a;
        font-size: 17px;
        font-weight: 800;
    }

    .ai-header p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 11px;
    }

    .ai-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ai-header-btn {
        padding: 9px 12px;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
        background: #fff;
        border: 1px solid var(--ai-border);
        border-radius: 9px;
        cursor: pointer;
    }

    .ai-header-btn:hover {
        color: #1d4ed8;
        background: #eff6ff;
    }

    .ai-messages {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        padding: 26px clamp(16px, 4vw, 46px);
        scroll-behavior: smooth;
    }

    .ai-welcome {
        max-width: 680px;
        margin: 15px auto 28px;
        text-align: center;
    }

    .ai-welcome-symbol {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 65px;
        height: 65px;
        margin: 0 auto 18px;
        color: #2563eb;
        font-size: 32px;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        border-radius: 22px;
    }

    .ai-welcome h2 {
        margin: 0;
        color: #0f172a;
        font-size: clamp(23px, 3vw, 30px);
        font-weight: 800;
        letter-spacing: -0.7px;
    }

    .ai-welcome > p {
        max-width: 470px;
        margin: 12px auto 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.8;
    }

    .ai-suggestions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-top: 28px;
        text-align: left;
    }

    .ai-suggestion {
        display: block;
        padding: 15px;
        color: #334155;
        text-align: left;
        background: #fff;
        border: 1px solid var(--ai-border);
        border-radius: 13px;
        cursor: pointer;
        transition: border-color .2s, background .2s, transform .2s;
    }

    .ai-suggestion:hover {
        background: #f8fbff;
        border-color: #93c5fd;
        transform: translateY(-2px);
    }

    .ai-suggestion strong {
        display: block;
        margin-bottom: 6px;
        color: #1e293b;
        font-size: 12px;
        font-weight: 750;
    }

    .ai-suggestion span {
        display: block;
        color: #64748b;
        font-size: 11px;
        line-height: 1.6;
    }

    .ai-message-row {
        display: flex;
        gap: 11px;
        max-width: 760px;
        margin: 0 auto 22px;
        align-items: flex-start;
    }

    .ai-message-row.user {
        flex-direction: row-reverse;
    }

    .ai-message-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        flex-shrink: 0;
        color: #fff;
        font-size: 15px;
        background: #2563eb;
        border-radius: 11px;
    }

    .ai-message-row.user .ai-message-avatar {
        background: #475569;
    }

    .ai-message-content {
        max-width: min(85%, 590px);
    }

    .ai-message-name {
        margin: 0 0 6px;
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
    }

    .ai-message-row.user .ai-message-name {
        text-align: right;
    }

    .ai-message-bubble {
        padding: 12px 15px;
        color: #334155;
        font-size: 13px;
        line-height: 1.75;
        overflow-wrap: anywhere;
        white-space: pre-wrap;
        background: #f1f5f9;
        border-radius: 4px 15px 15px 15px;
    }

    .ai-message-row.user .ai-message-bubble {
        color: #fff;
        background: #2563eb;
        border-radius: 15px 4px 15px 15px;
    }

    .ai-typing {
        display: inline-flex;
        gap: 4px;
        align-items: center;
    }

    .ai-typing span {
        width: 6px;
        height: 6px;
        background: #64748b;
        border-radius: 50%;
        animation: aiTyping 1s infinite ease-in-out;
    }

    .ai-typing span:nth-child(2) {
        animation-delay: .15s;
    }

    .ai-typing span:nth-child(3) {
        animation-delay: .3s;
    }

    @keyframes aiTyping {
        0%, 60%, 100% { transform: translateY(0); opacity: .5; }
        30% { transform: translateY(-4px); opacity: 1; }
    }

    .ai-composer-area {
        padding: 14px clamp(16px, 4vw, 46px) 18px;
        background: #fff;
        border-top: 1px solid #edf2f7;
    }

    .ai-composer {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        max-width: 760px;
        margin: 0 auto;
        padding: 9px;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 15px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, .04);
        transition: border-color .2s, box-shadow .2s;
    }

    .ai-composer:focus-within {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .1);
    }

    .ai-composer textarea {
        flex: 1;
        min-width: 0;
        min-height: 40px;
        max-height: 120px;
        padding: 10px 8px;
        color: #0f172a;
        font: inherit;
        font-size: 13px;
        line-height: 1.6;
        resize: none;
        outline: none;
        background: transparent;
        border: 0;
    }

    .ai-composer textarea::placeholder {
        color: #94a3b8;
    }

    .ai-send {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        color: #fff;
        font-size: 18px;
        background: #2563eb;
        border: 0;
        border-radius: 11px;
        cursor: pointer;
        transition: background .2s;
    }

    .ai-send:hover {
        background: #1d4ed8;
    }

    .ai-send:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    .ai-mic {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        color: #475569;
        font-size: 16px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 11px;
        cursor: pointer;
        transition: background .2s, color .2s, transform .1s;
    }

    .ai-mic:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    .ai-mic.recording {
        background: #ef4444 !important;
        color: #ffffff !important;
        border-color: #dc2626 !important;
        animation: aiMicPulse 1.2s infinite ease-in-out;
    }

    @keyframes aiMicPulse {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6); }
        50% { transform: scale(1.08); box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
    }

    .ai-disclaimer {
        margin: 10px 0 0;
        color: #94a3b8;
        font-size: 10px;
        line-height: 1.6;
        text-align: center;
    }

    @media (max-width: 850px) {
        .ai-shell {
            grid-template-columns: 190px minmax(0, 1fr);
        }

        .ai-sidebar {
            padding: 18px 11px;
        }

        .ai-header {
            padding: 15px;
        }

        .ai-header-btn {
            padding: 8px;
        }
    }

    @media (max-width: 640px) {
        .ai-shell {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 110px);
            min-height: 580px;
            max-height: none;
            border-radius: 14px;
        }

        .ai-sidebar {
            display: none;
        }

        .ai-header {
            padding: 13px;
        }

        .ai-header h1 {
            font-size: 14px;
        }

        .ai-header p {
            font-size: 10px;
        }

        .ai-avatar {
            width: 37px;
            height: 37px;
        }

        .ai-header-actions {
            gap: 5px;
        }

        .ai-header-btn {
            padding: 7px;
            font-size: 10px;
        }

        .ai-messages {
            padding: 20px 13px;
        }

        .ai-suggestions {
            grid-template-columns: 1fr;
            gap: 9px;
        }

        .ai-welcome {
            margin-top: 8px;
        }

        .ai-composer-area {
            padding: 12px;
        }

        .ai-message-content {
            max-width: 88%;
        }
    }
</style>

<div class="ai-page">
    <div class="ai-shell">

        {{-- Sidebar --}}
        <aside class="ai-sidebar">
            <div class="ai-brand">
                <div class="ai-brand-icon" aria-hidden="true">✦</div>
                <div>
                    <h2>Hospital AI</h2>
                    <p>Intelligent Assistant</p>
                </div>
            </div>

            <button type="button" class="ai-new-chat" id="aiNewChat">
                ＋ &nbsp; New conversation
            </button>

            <p class="ai-sidebar-heading">Workspace</p>

            <a href="{{ url('/dashboard') }}" class="ai-sidebar-item">
                <span class="ai-nav-icon">⌂</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ url('/ai-assistant') }}"
               class="ai-sidebar-item active"
               aria-current="page">
                <span class="ai-nav-icon">✦</span>
                <span>AI Assistant</span>
            </a>

            <p class="ai-sidebar-heading">Assistant tools</p>

            <button type="button"
                    class="ai-sidebar-item"
                    style="width:100%;border:0;background:transparent;cursor:pointer;text-align:left;"
                    data-ai-question="What hospital services should I know about?">
                <span class="ai-nav-icon">♡</span>
                <span>Hospital services</span>
            </button>

            <button type="button"
                    class="ai-sidebar-item"
                    style="width:100%;border:0;background:transparent;cursor:pointer;text-align:left;"
                    data-ai-question="How can I manage appointments in a hospital system?">
                <span class="ai-nav-icon">▦</span>
                <span>Appointment help</span>
            </button>

            <button type="button"
                    class="ai-sidebar-item"
                    style="width:100%;border:0;background:transparent;cursor:pointer;text-align:left;"
                    data-ai-question="What are good practices for protecting patient information?">
                <span class="ai-nav-icon">♧</span>
                <span>Patient data safety</span>
            </button>

            <div class="ai-sidebar-footer">
                <div class="ai-status">
                    <span class="ai-status-dot"></span>
                    Demo mode
                </div>
                <p>
                    The interface is ready. Real AI responses require a configured
                    backend service.
                </p>
            </div>
        </aside>

        {{-- Main chat --}}
        <main class="ai-main">
            <header class="ai-header">
                <div class="ai-header-title">
                    <div class="ai-avatar" aria-hidden="true">✦</div>
                    <div>
                        <h1>AI Hospital Assistant</h1>
                        <p>Hospital information and management support</p>
                    </div>
                </div>

                <div class="ai-header-actions">
                    <button type="button" class="ai-header-btn" id="aiVoiceToggle" title="Toggle voice dictation (read responses aloud)">
                        <span id="aiVoiceIcon">🔊</span>
                        <span id="aiVoiceStatus">Voice ON</span>
                    </button>
                    <button type="button" class="ai-header-btn" id="aiClearChat">
                        Clear chat
                    </button>
                    <a href="{{ url('/dashboard') }}"
                       class="ai-header-btn"
                       style="text-decoration:none;">
                        Dashboard
                    </a>
                </div>
            </header>

            <section class="ai-messages"
                     id="aiMessages"
                     aria-live="polite"
                     aria-label="Chat messages">

                <div class="ai-welcome" id="aiWelcome">
                    <div class="ai-welcome-symbol" aria-hidden="true">✦</div>
                    <h2>How can I help you today?</h2>
                    <p>
                        Welcome to your Hospital AI Assistant. Ask a question or
                        select a suggested topic to get started.
                    </p>

                    <div class="ai-suggestions">
                        <button type="button"
                                class="ai-suggestion"
                                data-ai-question="How can hospital appointments be managed efficiently?">
                            <strong>▦ &nbsp; Appointment management</strong>
                            <span>Learn about appointment scheduling and organization.</span>
                        </button>

                        <button type="button"
                                class="ai-suggestion"
                                data-ai-question="What information is normally included in a patient record?">
                            <strong>♡ &nbsp; Patient records</strong>
                            <span>Understand common patient record information.</span>
                        </button>

                        <button type="button"
                                class="ai-suggestion"
                                data-ai-question="How can a hospital improve its daily workflow?">
                            <strong>⌁ &nbsp; Hospital workflow</strong>
                            <span>Explore general ways to organize hospital operations.</span>
                        </button>

                        <button type="button"
                                class="ai-suggestion"
                                data-ai-question="What are important patient data privacy practices?">
                            <strong>♧ &nbsp; Data privacy</strong>
                            <span>Review basic practices for protecting patient data.</span>
                        </button>
                    </div>
                </div>

            </section>

            {{-- Message composer --}}
            <div class="ai-composer-area">
                <!-- Voice Status / Listening Indicator -->
                <div id="aiVoiceIndicator" style="display:none; margin-bottom:8px; padding:6px 12px; background:#fff1f2; border:1px solid #fecdd3; border-radius:10px; color:#e11d48; font-size:12px; font-weight:600; align-items:center; justify-content:space-between;">
                    <span>🎙️ Listening... Speak into your microphone (click Mic again to send)</span>
                    <button type="button" id="aiCancelVoiceBtn" style="background:transparent; border:0; color:#e11d48; cursor:pointer; font-size:11px; font-weight:700; text-transform:uppercase;">Cancel</button>
                </div>

                <!-- Speaking Aloud Banner -->
                <div id="aiSpeakingBanner" style="display:none; margin-bottom:8px; padding:6px 12px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; color:#1d4ed8; font-size:12px; font-weight:600; align-items:center; justify-content:space-between;">
                    <span>🔊 Reading response aloud through speaker...</span>
                    <button type="button" id="aiStopSpeakingBtn" style="background:#dbeafe; border:0; color:#1d4ed8; border-radius:6px; padding:2px 8px; cursor:pointer; font-size:10px; font-weight:700; text-transform:uppercase;">Stop Voice</button>
                </div>

                <form class="ai-composer" id="aiChatForm" style="display:flex; gap:8px; align-items:flex-end;">
                    <textarea
                        id="aiChatInput"
                        rows="1"
                        maxlength="2000"
                        placeholder="Ask your hospital assistant a question or click the mic to talk..."
                        aria-label="Type your message"
                        required></textarea>

                    <button type="button"
                            class="ai-mic"
                            id="aiMicButton"
                            title="Speak to Assistant (Voice Input)"
                            aria-label="Speak into microphone">
                        <i class="fa-solid fa-microphone"></i>
                    </button>

                    <button type="submit"
                            class="ai-send"
                            id="aiSendButton"
                            aria-label="Send message">
                        ↑
                    </button>
                </form>

                <p class="ai-disclaimer">
                    Hospital AI Clinical Assistant. Responses are generated with AI and clinical database tools.
                </p>
            </div>
        </main>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('aiChatForm');
    const input = document.getElementById('aiChatInput');
    const messages = document.getElementById('aiMessages');
    const welcome = document.getElementById('aiWelcome');
    const sendButton = document.getElementById('aiSendButton');
    const newChatButton = document.getElementById('aiNewChat');
    const clearChatButton = document.getElementById('aiClearChat');

    if (!form || !input || !messages) {
        return;
    }

    /*
     * This is a frontend demonstration.
     * Replace demoReply() with a request to your Laravel backend
     * when you connect a real AI service.
     */

    function addMessage(text, sender) {
        const row = document.createElement('div');
        row.className = 'ai-message-row ' + sender;

        const avatar = document.createElement('div');
        avatar.className = 'ai-message-avatar';
        avatar.textContent = sender === 'user' ? 'U' : '✦';
        avatar.setAttribute('aria-hidden', 'true');

        const content = document.createElement('div');
        content.className = 'ai-message-content';

        const name = document.createElement('p');
        name.className = 'ai-message-name';
        name.textContent = sender === 'user' ? 'You' : 'Hospital AI Assistant';

        const bubble = document.createElement('div');
        bubble.className = 'ai-message-bubble';
        bubble.textContent = text;

        content.appendChild(name);
        content.appendChild(bubble);
        row.appendChild(avatar);
        row.appendChild(content);
        messages.appendChild(row);

        return row;
    }

    function showTyping() {
        const row = document.createElement('div');
        row.className = 'ai-message-row';
        row.id = 'aiTypingIndicator';

        const avatar = document.createElement('div');
        avatar.className = 'ai-message-avatar';
        avatar.textContent = '✦';

        const content = document.createElement('div');
        content.className = 'ai-message-content';

        const name = document.createElement('p');
        name.className = 'ai-message-name';
        name.textContent = 'Hospital AI Assistant';

        const bubble = document.createElement('div');
        bubble.className = 'ai-message-bubble';

        const typing = document.createElement('span');
        typing.className = 'ai-typing';
        typing.setAttribute('aria-label', 'Assistant is preparing a response');

        for (let i = 0; i < 3; i++) {
            typing.appendChild(document.createElement('span'));
        }

        bubble.appendChild(typing);
        content.appendChild(name);
        content.appendChild(bubble);
        row.appendChild(avatar);
        row.appendChild(content);
        messages.appendChild(row);

        messages.scrollTop = messages.scrollHeight;
        return row;
    }

    function demoReply(question) {
        const q = question.toLowerCase();

        if (q.includes('appointment') || q.includes('schedule')) {
            return 'Appointment management generally includes recording the appointment date and time, linking the appointment to the correct patient and provider, checking availability, and updating the appointment status. Use the features available in your hospital application to manage actual appointments.';
        }

        if (q.includes('patient record') || q.includes('patient information')) {
            return 'A patient record may include a patient identifier, contact information, relevant medical history, allergies, visit details, and authorized clinical notes. Store only necessary information and restrict access to authorized users. This demo cannot access your actual patient database.';
        }

        if (q.includes('privacy') || q.includes('security') || q.includes('data safety')) {
            return 'Good patient-data protection practices include role-based access control, strong authentication, secure connections, appropriate database permissions, audit logs, backups, and avoiding sensitive patient information in public chat tools. Follow your organization’s applicable privacy requirements.';
        }

        if (q.includes('workflow') || q.includes('hospital')) {
            return 'Hospital workflows can be improved by clearly defining staff responsibilities, keeping appointment and patient records up to date, reducing duplicate data entry, using appropriate access permissions, and documenting important process changes. I cannot inspect your application data from this demo page.';
        }

        if (q.includes('hello') || q.includes('hi ') || q === 'hi' || q === 'hello') {
            return 'Hello! I can demonstrate general hospital-management guidance, including appointments, patient records, workflow, and data privacy. What would you like to know?';
        }

        return 'Thanks for your question. This assistant is currently running in demo mode, so it does not use a real AI model or read your hospital database. To answer your question intelligently, the next step is to connect this page to a Laravel backend endpoint and configure an AI service securely.';
    }

    let currentConversationId = null;

    async function submitQuestion(question) {
        const cleaned = question.trim();

        if (!cleaned || sendButton.disabled) {
            return;
        }

        stopSpeaking();
        stopVoiceRecognition();

        if (welcome && welcome.isConnected) {
            welcome.remove();
        }

        addMessage(cleaned, 'user');
        input.value = '';
        input.style.height = 'auto';
        sendButton.disabled = true;

        const typing = showTyping();
        messages.scrollTop = messages.scrollHeight;

        try {
            const res = await fetch("{{ route('ai.chat') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    message: cleaned,
                    conversation_id: currentConversationId
                })
            });

            const data = await res.json();
            if (typing.isConnected) {
                typing.remove();
            }

            if (data.conversation_id) {
                currentConversationId = data.conversation_id;
            }

            const reply = data.message || 'I could not process that request. Please try again.';
            addMessage(reply, 'assistant');
            // Dictate response aloud through speaker if active
            speakResponse(reply);

        } catch (err) {
            if (typing.isConnected) {
                typing.remove();
            }
            addMessage('Network error communicating with AI assistant. Please try again.', 'assistant');
            console.error('AI error:', err);
        } finally {
            sendButton.disabled = false;
            input.focus();
            messages.scrollTop = messages.scrollHeight;
        }
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        submitQuestion(input.value);
    });

    input.addEventListener('input', function () {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 120) + 'px';
    });

    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    document.querySelectorAll('[data-ai-question]').forEach(function (button) {
        button.addEventListener('click', function () {
            const question = button.getAttribute('data-ai-question');

            if (question) {
                submitQuestion(question);
            }
        });
    });

    function resetChat() {
        stopSpeaking();
        stopVoiceRecognition();
        currentConversationId = null;
        messages.replaceChildren();

        const intro = document.createElement('div');
        intro.className = 'ai-welcome';
        intro.id = 'aiWelcome';

        const symbol = document.createElement('div');
        symbol.className = 'ai-welcome-symbol';
        symbol.textContent = '✦';

        const heading = document.createElement('h2');
        heading.textContent = 'How can I help you today?';

        const paragraph = document.createElement('p');
        paragraph.textContent = 'Conversation has been cleared. Ask a question or click the microphone to talk.';

        intro.appendChild(symbol);
        intro.appendChild(heading);
        intro.appendChild(paragraph);
        messages.appendChild(intro);

        input.value = '';
        input.style.height = 'auto';
        sendButton.disabled = false;
        input.focus();
    }

    newChatButton.addEventListener('click', resetChat);
    clearChatButton.addEventListener('click', resetChat);

    // ==========================================
    // VOICE RECOGNITION (SPEECH-TO-TEXT) & TTS
    // ==========================================
    const micButton = document.getElementById('aiMicButton');
    const voiceIndicator = document.getElementById('aiVoiceIndicator');
    const cancelVoiceBtn = document.getElementById('aiCancelVoiceBtn');
    const voiceToggleBtn = document.getElementById('aiVoiceToggle');
    const voiceIcon = document.getElementById('aiVoiceIcon');
    const voiceStatus = document.getElementById('aiVoiceStatus');
    const speakingBanner = document.getElementById('aiSpeakingBanner');
    const stopSpeakingBtn = document.getElementById('aiStopSpeakingBtn');

    let recognition = null;
    let isRecording = false;
    let speakerActive = localStorage.getItem('apex_ai_voice_dictation') !== 'false'; // default ON

    const SpeechRecClass = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (SpeechRecClass) {
        recognition = new SpeechRecClass();
        recognition.continuous = false;
        recognition.interimResults = true;
        recognition.lang = 'en-US';

        recognition.onstart = function () {
            isRecording = true;
            updateMicUI(true);
        };

        recognition.onresult = function (event) {
            let transcript = '';
            for (let i = event.resultIndex; i < event.results.length; ++i) {
                transcript += event.results[i][0].transcript;
            }
            if (transcript) {
                input.value = transcript;
                input.style.height = 'auto';
                input.style.height = Math.min(input.scrollHeight, 120) + 'px';
            }
        };

        recognition.onerror = function (event) {
            console.warn('Speech recognition error:', event.error);
            stopVoiceRecognition();
            if (event.error === 'not-allowed') {
                alert('Microphone permission was denied. Please allow microphone access in your browser settings.');
            }
        };

        recognition.onend = function () {
            isRecording = false;
            updateMicUI(false);
            if (input.value.trim().length > 0) {
                submitQuestion(input.value);
            }
        };
    }

    function toggleVoiceRecognition() {
        if (!recognition) {
            alert('Speech recognition is not supported in this browser. Please use Chrome, Edge, or Safari.');
            return;
        }

        stopSpeaking();

        if (isRecording) {
            stopVoiceRecognition();
        } else {
            try {
                recognition.start();
            } catch (err) {
                console.warn('Recognition start error:', err);
            }
        }
    }

    function stopVoiceRecognition() {
        if (recognition && isRecording) {
            try {
                recognition.stop();
            } catch (e) {}
        }
        isRecording = false;
        updateMicUI(false);
    }

    function updateMicUI(recording) {
        if (!micButton) return;
        if (recording) {
            micButton.classList.add('recording');
            micButton.innerHTML = '<i class="fa-solid fa-stop"></i>';
            micButton.title = 'Listening... Click to finish and send';
            if (voiceIndicator) voiceIndicator.style.display = 'flex';
        } else {
            micButton.classList.remove('recording');
            micButton.innerHTML = '<i class="fa-solid fa-microphone"></i>';
            micButton.title = 'Speak to Assistant (Voice Input)';
            if (voiceIndicator) voiceIndicator.style.display = 'none';
        }
    }

    if (micButton) {
        micButton.addEventListener('click', toggleVoiceRecognition);
    }

    if (cancelVoiceBtn) {
        cancelVoiceBtn.addEventListener('click', function () {
            input.value = '';
            stopVoiceRecognition();
        });
    }

    // Voice Dictation (Speaker)
    function updateSpeakerToggleUI() {
        if (speakerActive) {
            if (voiceIcon) voiceIcon.textContent = '🔊';
            if (voiceStatus) voiceStatus.textContent = 'Voice ON';
            if (voiceToggleBtn) {
                voiceToggleBtn.title = 'Voice Dictation is Active. Click to Mute.';
                voiceToggleBtn.style.color = '#2563eb';
            }
        } else {
            if (voiceIcon) voiceIcon.textContent = '🔇';
            if (voiceStatus) voiceStatus.textContent = 'Voice Muted';
            if (voiceToggleBtn) {
                voiceToggleBtn.title = 'Voice Dictation is Muted. Click to Turn ON.';
                voiceToggleBtn.style.color = '#64748b';
            }
        }
    }

    if (voiceToggleBtn) {
        voiceToggleBtn.addEventListener('click', function () {
            speakerActive = !speakerActive;
            localStorage.setItem('apex_ai_voice_dictation', speakerActive ? 'true' : 'false');
            if (!speakerActive) {
                stopSpeaking();
            }
            updateSpeakerToggleUI();
        });
    }

    function speakResponse(rawText) {
        if (!speakerActive || !('speechSynthesis' in window)) return;

        window.speechSynthesis.cancel();

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

        utterance.onstart = function () {
            if (speakingBanner) speakingBanner.style.display = 'flex';
        };

        utterance.onend = function () {
            if (speakingBanner) speakingBanner.style.display = 'none';
        };

        utterance.onerror = function (e) {
            console.warn('SpeechSynthesis error:', e);
            if (speakingBanner) speakingBanner.style.display = 'none';
        };

        window.speechSynthesis.speak(utterance);
    }

    function stopSpeaking() {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
        }
        if (speakingBanner) speakingBanner.style.display = 'none';
    }

    if (stopSpeakingBtn) {
        stopSpeakingBtn.addEventListener('click', stopSpeaking);
    }

    updateSpeakerToggleUI();
})();
</script>

@endsection