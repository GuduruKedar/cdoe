<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Online MBA &amp; BBA Portal — VFSTR Vignan CDOE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brand-blue: #2563eb;
            --brand-blue-hover: #1d4ed8;
            --brand-cyan: #38bdf8;
            --text-heading: #0f172a;
            --text-body: #475569;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
            min-height: 100vh;
            background-color: #0d2757;
            background-image:
                linear-gradient(180deg, rgba(16, 52, 120, 0.48) 0%, rgba(10, 36, 88, 0.65) 50%, rgba(6, 22, 58, 0.82) 100%),
                url('{{ asset("images/vignan-campus.jpg") }}');
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 36px 28px;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient subtle light spots */
        .ambient-glow {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
            z-index: 0;
            opacity: 0.35;
        }
        .glow-top-left {
            top: -120px;
            left: -100px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #38bdf8 0%, transparent 70%);
        }
        .glow-bottom-right {
            bottom: -150px;
            right: -50px;
            width: 550px;
            height: 550px;
            background: radial-gradient(circle, #2563eb 0%, transparent 70%);
        }

        /* Main Container */
        .portal-layout-container {
            width: 100%;
            max-width: 1220px;
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            align-items: center;
            gap: 52px;
            position: relative;
            z-index: 2;
            margin: auto;
        }

        /* ================= LEFT COLUMN: HERO & FEATURE CARDS ================= */
        .portal-hero-section {
            display: flex;
            flex-direction: column;
        }

        /* Pill Badge: VFSTR • VIGNAN UNIVERSITY */
        .university-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.32);
            border-radius: 999px;
            padding: 7px 18px 7px 10px;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.4px;
            width: fit-content;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.20);
        }
        .badge-emblem-icon {
            width: 24px;
            height: 24px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.35));
        }

        /* Hero Main Title */
        .hero-portal-title {
            font-size: 46px;
            font-weight: 800;
            line-height: 1.12;
            letter-spacing: -0.7px;
            color: #ffffff;
            margin-bottom: 14px;
            text-shadow: 0 3px 12px rgba(0, 0, 0, 0.35);
        }
        .hero-portal-title .highlight-cyan {
            color: var(--brand-cyan);
        }

        .hero-portal-desc {
            font-size: 15px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.94);
            max-width: 560px;
            margin-bottom: 28px;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.35);
        }

        /* Feature Cards Grid (2x2 + 1 Full Width) */
        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            width: 100%;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-radius: 16px;
            padding: 16px 18px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            border: 1px solid rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 25px -4px rgba(0, 0, 0, 0.16);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .feature-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px -4px rgba(0, 0, 0, 0.22);
        }

        .feature-card.full-width {
            grid-column: 1 / -1;
        }

        /* Feature Icon Box */
        .feature-icon {
            width: 44px;
            height: 44px;
            min-width: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }
        .bg-icon-blue { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
        .bg-icon-cyan { background: linear-gradient(135deg, #06b6d4, #0891b2); }
        .bg-icon-green { background: linear-gradient(135deg, #10b981, #059669); }
        .bg-icon-orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .bg-icon-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

        .feature-body {
            flex: 1;
            min-width: 0;
        }
        .feature-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 4px;
        }
        .feature-name {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.3;
        }
        .feature-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }
        .feature-badge .dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            display: inline-block;
        }

        .badge-blue { background: #eff6ff; color: #1d4ed8; }
        .badge-blue .dot { background: #2563eb; }

        .badge-cyan { background: #ecfeff; color: #0891b2; }
        .badge-cyan .dot { background: #06b6d4; }

        .badge-green { background: #ecfdf5; color: #047857; }
        .badge-green .dot { background: #10b981; }

        .badge-orange { background: #fffbeb; color: #b45309; }
        .badge-orange .dot { background: #f59e0b; }

        .badge-purple { background: #f5f3ff; color: #6d28d9; }
        .badge-purple .dot { background: #8b5cf6; }

        .feature-desc {
            font-size: 12px;
            line-height: 1.45;
            color: #64748b;
            margin: 0;
        }

        /* ================= RIGHT COLUMN: AUTH CARD ================= */
        .auth-card-wrapper {
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
        }

        .auth-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 38px 32px 30px;
            box-shadow:
                0 30px 70px -10px rgba(0, 0, 0, 0.42),
                0 15px 35px -5px rgba(0, 0, 0, 0.16),
                0 0 0 1px rgba(255, 255, 255, 0.9) inset;
            position: relative;
        }

        .auth-card-header {
            text-align: center;
            margin-bottom: 22px;
        }

        .portal-shield-img {
            width: 56px;
            height: 56px;
            object-fit: contain;
            margin: 0 auto 10px;
            display: block;
            filter: drop-shadow(0 3px 6px rgba(0, 0, 0, 0.08));
        }

        .auth-institution-tag {
            font-size: 11px;
            font-weight: 700;
            color: #2563eb;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin-bottom: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .auth-portal-title {
            font-size: 25px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.4px;
            margin-bottom: 5px;
            line-height: 1.2;
        }
        .auth-portal-title .portal-blue {
            color: #2563eb;
        }

        .auth-portal-sub {
            font-size: 13.5px;
            color: #64748b;
            font-weight: 500;
            margin: 0;
        }

        /* Capsule Tab Switcher */
        .auth-nav-tabs {
            display: flex;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 20px;
        }
        .auth-tab-btn {
            flex: 1;
            padding: 8px 16px;
            border-radius: 9px;
            font-size: 13.5px;
            font-weight: 700;
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            outline: none;
        }
        .auth-tab-btn.active {
            background: #ffffff;
            color: #1e293b;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        /* Alerts */
        .alert-box {
            padding: 11px 13px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            line-height: 1.45;
        }
        .alert-box.alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .alert-box.alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 18px;
        }
        .form-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 7px;
        }
        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrap .field-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 17px;
            pointer-events: none;
            transition: color 0.2s ease;
        }
        .portal-input {
            width: 100%;
            height: 48px;
            padding: 10px 14px 10px 44px;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            font-size: 14px;
            color: #0f172a;
            font-family: inherit;
            outline: none;
            transition: all 0.2s ease;
        }
        .portal-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3.5px rgba(37, 99, 235, 0.14);
        }
        .portal-input:focus ~ .field-icon,
        .input-wrap:focus-within .field-icon {
            color: #2563eb;
        }
        .portal-input::placeholder {
            color: #94a3b8;
            font-size: 13.5px;
        }

        /* 6-digit OTP input */
        .otp-input-field {
            height: 52px;
            font-size: 24px !important;
            letter-spacing: 10px;
            text-align: center;
            padding-left: 18px !important;
            font-weight: 700;
            color: #0f172a;
        }

        /* Quick Account Chips */
        .quick-chips-row {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }
        .chip-label {
            font-size: 11.5px;
            color: #64748b;
            font-weight: 600;
        }
        .quick-chip {
            padding: 4px 10px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 11.5px;
            color: #334155;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            font-family: inherit;
        }
        .quick-chip:hover {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #2563eb;
            transform: translateY(-1px);
        }

        /* Primary Blue Button */
        .btn-portal-submit {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.38);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            outline: none;
            letter-spacing: 0.2px;
        }
        .btn-portal-submit:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.45);
        }
        .btn-portal-submit:active {
            transform: translateY(1px);
        }
        .btn-portal-submit:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        .btn-back-outline {
            width: 100%;
            margin-top: 10px;
            height: 40px;
            background: transparent;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-back-outline:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        .portal-helper-note {
            font-size: 12px;
            color: #64748b;
            text-align: center;
            margin-top: 14px;
        }

        /* Register Tab Pane */
        .register-container {
            animation: fadeIn 0.2s ease-in-out;
        }
        .register-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .register-text {
            font-size: 13px;
            color: #475569;
            line-height: 1.5;
            margin-bottom: 16px;
        }
        .register-contact-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3.5px solid #2563eb;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 16px;
        }
        .contact-box-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .contact-row {
            font-size: 13px;
            color: #334155;
            margin-bottom: 8px;
            display: flex;
            gap: 6px;
        }
        .contact-row:last-child { margin-bottom: 0; }
        .contact-row strong { color: #0f172a; }
        .contact-row a { color: #2563eb; text-decoration: none; font-weight: 600; }
        .contact-row a:hover { text-decoration: underline; }

        .btn-return-login {
            width: 100%;
            padding: 10px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #2563eb;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-return-login:hover {
            background: #dbeafe;
        }

        /* Card Bottom Copyright */
        .auth-card-footer {
            margin-top: 22px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }
        .auth-card-footer .powered-by {
            font-size: 11.5px;
            color: #94a3b8;
            margin-top: 2px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(3px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hidden { display: none !important; }

        /* Responsive Breakpoints */
        @media (max-width: 1024px) {
            .portal-hero-section {
                display: none !important;
            }
            .portal-layout-container {
                grid-template-columns: 1fr;
                justify-content: center;
                align-items: center;
                margin: auto;
                padding: 0;
                gap: 0;
                max-width: 460px;
            }
            .auth-card-wrapper {
                max-width: 100%;
                width: 100%;
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 16px 14px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .auth-card {
                padding: 32px 22px 26px;
                border-radius: 22px;
                box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.45);
            }
            .portal-shield-img {
                width: 50px;
                height: 50px;
            }
            .auth-portal-title {
                font-size: 22px;
            }
            .auth-portal-sub {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>

    <div class="ambient-glow glow-top-left" aria-hidden="true"></div>
    <div class="ambient-glow glow-bottom-right" aria-hidden="true"></div>

    <div class="portal-layout-container">

        {{-- ================= LEFT HERO & FEATURE CARDS (Online MBA & BBA) ================= --}}
        <section class="portal-hero-section">

            {{-- University Pill Badge --}}
            <div class="university-badge-pill">
                <img src="{{ asset('images/vignan-emblem.png') }}" alt="Vignan Shield" class="badge-emblem-icon">
                <span>VFSTR • CDOE ONLINE MBA &amp; BBA</span>
            </div>

            {{-- Large Headline --}}
            <h1 class="hero-portal-title">
                Online MBA &amp; BBA <span class="highlight-cyan">Portal</span>
            </h1>

            <p class="hero-portal-desc">
                A unified digital campus platform empowering Online MBA &amp; BBA learners with industry-aligned curriculum, interactive live lectures, case studies, and corporate mentor connectivity.
            </p>

            {{-- Feature Cards Grid (Customized for Online MBA & BBA) --}}
            <div class="features-grid">

                {{-- Card 1: Specializations & Electives --}}
                <div class="feature-card">
                    <div class="feature-icon bg-icon-blue">
                        <i class="bi bi-briefcase-fill"></i>
                    </div>
                    <div class="feature-body">
                        <div class="feature-header">
                            <span class="feature-name">Specializations &amp; Electives</span>
                            <span class="feature-badge badge-blue">
                                <span class="dot"></span> MBA &amp; BBA
                            </span>
                        </div>
                        <p class="feature-desc">Dual specializations in Finance, Marketing, HR, Operations, and Business Analytics.</p>
                    </div>
                </div>

                {{-- Card 2: Digital Study Materials --}}
                <div class="feature-card">
                    <div class="feature-icon bg-icon-cyan">
                        <i class="bi bi-journal-album"></i>
                    </div>
                    <div class="feature-body">
                        <div class="feature-header">
                            <span class="feature-name">Digital Study Materials</span>
                            <span class="feature-badge badge-cyan">
                                <span class="dot"></span> 24/7 E-Learning
                            </span>
                        </div>
                        <p class="feature-desc">Instant access to case studies, e-books, video lectures, and business journals.</p>
                    </div>
                </div>

                {{-- Card 3: Corporate Mentors & Faculty --}}
                <div class="feature-card">
                    <div class="feature-icon bg-icon-green">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="feature-body">
                        <div class="feature-header">
                            <span class="feature-name">Corporate Mentors &amp; Faculty</span>
                            <span class="feature-badge badge-green">
                                <span class="dot"></span> Direct Sync
                            </span>
                        </div>
                        <p class="feature-desc">Connect with seasoned management faculty, industry CXOs, and academic advisors.</p>
                    </div>
                </div>

                {{-- Card 4: Industry Projects & Case Studies --}}
                <div class="feature-card">
                    <div class="feature-icon bg-icon-orange">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div class="feature-body">
                        <div class="feature-header">
                            <span class="feature-name">Live Projects &amp; Case Studies</span>
                            <span class="feature-badge badge-orange">
                                <span class="dot"></span> Practical Track
                            </span>
                        </div>
                        <p class="feature-desc">Integrated corporate simulations, capstone projects, and real-world business case analyses.</p>
                    </div>
                </div>

                {{-- Card 5: Academic Progress & Tracking (Full width) --}}
                <div class="feature-card full-width">
                    <div class="feature-icon bg-icon-purple">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <div class="feature-body">
                        <div class="feature-header">
                            <span class="feature-name">Examinations &amp; Academic Progress</span>
                            <span class="feature-badge badge-purple">
                                <span class="dot"></span> Real-Time
                            </span>
                        </div>
                        <p class="feature-desc">Track semester SGPA/CGPA, continuous internal assessments, proctored examinations, and marks memos.</p>
                    </div>
                </div>

            </div>

        </section>

        {{-- ================= RIGHT AUTH CARD ================= --}}
        <div class="auth-card-wrapper">
            <main class="auth-card">

                {{-- Header with Vignan Emblem --}}
                <div class="auth-card-header">
                    <img src="{{ asset('images/vignan-emblem.png') }}"
                         alt="VFSTR Vignan University"
                         class="portal-shield-img">
                    <div class="auth-institution-tag">
                        <span>• VFSTR • ONLINE MBA &amp; BBA (CDOE)</span>
                    </div>
                    <h2 class="auth-portal-title">
                        Online MBA &amp; BBA <span class="portal-blue">Portal</span>
                    </h2>
                    <p class="auth-portal-sub">Welcome Back! Please enter your credentials to continue.</p>
                </div>

                {{-- Navigation Tabs (Login / Register) --}}
                <div class="auth-nav-tabs" role="tablist">
                    <button type="button"
                            class="auth-tab-btn active"
                            id="tab-login-btn"
                            role="tab"
                            aria-selected="true"
                            onclick="switchAuthTab('login')">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Login</span>
                    </button>
                    <button type="button"
                            class="auth-tab-btn"
                            id="tab-register-btn"
                            role="tab"
                            aria-selected="false"
                            onclick="switchAuthTab('register')">
                        <i class="bi bi-person-lines-fill"></i>
                        <span>Register</span>
                    </button>
                </div>

                {{-- Alert Notification Box --}}
                <div id="alert-box"
                     class="alert-box alert-error hidden"
                     role="alert"
                     aria-live="polite">
                    <i class="bi bi-exclamation-circle-fill" id="alert-icon"></i>
                    <span id="alert-text"></span>
                </div>

                {{-- ================= TAB 1: LOGIN ================= --}}
                <div id="pane-login" role="tabpanel">

                    {{-- Step 1: User Code / Register Number Form --}}
                    <form id="login-form" method="POST" action="{{ route('send.otp') }}">
                        @csrf

                        <div class="form-group">
                            <label for="usercode" class="form-label">
                                <span>User Code / Register Number</span>
                            </label>
                            <div class="input-wrap">
                                <i class="bi bi-person field-icon"></i>
                                <input type="text"
                                       id="usercode"
                                       name="usercode"
                                       class="portal-input"
                                       placeholder="e.g. 261CK01001 or staff code"
                                       value="{{ old('usercode') }}"
                                       autocomplete="username"
                                       required
                                       autofocus>
                            </div>
                        </div>


                        <button type="submit" id="send-otp-btn" class="btn-portal-submit">
                            <span>Sign In</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>

                    {{-- Step 2: OTP Verification Form --}}
                    <form id="otp-form" method="POST" action="{{ route('verify.otp') }}" class="hidden">
                        @csrf

                        <div class="form-group">
                            <label for="otp" class="form-label" id="otp-label">
                                <span>Enter the 6-digit OTP</span>
                            </label>
                            <div class="input-wrap">
                                <input type="text"
                                       id="otp"
                                       name="otp"
                                       class="portal-input otp-input-field"
                                       maxlength="6"
                                       pattern="[0-9]{6}"
                                       inputmode="numeric"
                                       autocomplete="one-time-code"
                                       placeholder="••••••"
                                       title="Enter the 6-digit OTP"
                                       required>
                            </div>
                        </div>

                        <button type="submit" id="verify-btn" class="btn-portal-submit">
                            <span>Verify &amp; Sign In</span>
                            <i class="bi bi-check2-circle"></i>
                        </button>

                        <button type="button" id="back-btn" class="btn-back-outline">
                            <i class="bi bi-arrow-left"></i>
                            <span>Back to Sign In</span>
                        </button>
                    </form>

                    <div class="portal-helper-note" id="otp-note">
                        Demo login mode • OTP valid for 5 minutes
                    </div>

                </div>

                {{-- ================= TAB 2: REGISTER ================= --}}
                <div id="pane-register" class="register-container hidden" role="tabpanel">
                    <h3 class="register-title">Registration</h3>
                    <p class="register-text">
                        Online MBA &amp; BBA learner registration and course admissions are managed by CDOE administration.
                    </p>

                    <div class="register-contact-box">
                        <div class="contact-box-title">
                            <i class="bi bi-info-circle-fill text-primary"></i>
                            <span>CDOE Admissions &amp; Records Office</span>
                        </div>

                        <div class="contact-row">
                            <strong>Name:</strong>
                            <span>Mr. D. V. N. S. M. Karthik</span>
                        </div>

                        <div class="contact-row">
                            <strong>Email:</strong>
                            <a href="mailto:dmk_cdoe@vignan.ac.in">dmk_cdoe@vignan.ac.in</a>
                        </div>

                        <div class="contact-row">
                            <strong>Phone:</strong>
                            <a href="tel:8886852292">8886852292</a>
                        </div>
                    </div>

                    <button type="button" class="btn-return-login" onclick="switchAuthTab('login')">
                        <i class="bi bi-arrow-left"></i>
                        <span>Return to Sign In</span>
                    </button>
                </div>

                {{-- Auth Card Footer --}}
                <div class="auth-card-footer">
                    <p>© {{ now()->year }} Centre for Distance &amp; Online Education (CDOE)</p>
                    <p class="powered-by">VFSTR Deemed to be University • Powered by Technology Development (TD)</p>
                </div>

            </main>
        </div>

    </div>

    {{-- ================= JAVASCRIPT CONTROLLERS ================= --}}
    <script>
        const loginForm = document.getElementById('login-form');
        const otpForm = document.getElementById('otp-form');
        const alertBox = document.getElementById('alert-box');
        const alertText = document.getElementById('alert-text');
        const alertIcon = document.getElementById('alert-icon');
        const otpInput = document.getElementById('otp');
        const otpLabel = document.getElementById('otp-label');
        const otpNote = document.getElementById('otp-note');
        const tabLoginBtn = document.getElementById('tab-login-btn');
        const tabRegisterBtn = document.getElementById('tab-register-btn');
        const paneLogin = document.getElementById('pane-login');
        const paneRegister = document.getElementById('pane-register');
        const usercodeInput = document.getElementById('usercode');

        function fillCode(code) {
            usercodeInput.value = code;
            usercodeInput.focus();
        }

        function switchAuthTab(tab) {
            hideAlert();
            if (tab === 'register') {
                tabLoginBtn.classList.remove('active');
                tabLoginBtn.setAttribute('aria-selected', 'false');
                tabRegisterBtn.classList.add('active');
                tabRegisterBtn.setAttribute('aria-selected', 'true');
                paneLogin.classList.add('hidden');
                paneRegister.classList.remove('hidden');
            } else {
                tabRegisterBtn.classList.remove('active');
                tabRegisterBtn.setAttribute('aria-selected', 'false');
                tabLoginBtn.classList.add('active');
                tabLoginBtn.setAttribute('aria-selected', 'true');
                paneRegister.classList.add('hidden');
                paneLogin.classList.remove('hidden');
                usercodeInput.focus();
            }
        }

        function showAlert(text, type = 'error') {
            alertText.textContent = text;
            alertBox.className = 'alert-box alert-' + type;
            if (type === 'success') {
                alertIcon.className = 'bi bi-check-circle-fill';
            } else {
                alertIcon.className = 'bi bi-exclamation-circle-fill';
            }
        }

        function hideAlert() {
            alertBox.className = 'alert-box hidden';
            alertText.textContent = '';
        }

        async function submitAjax(form, button, onSuccess) {
            const originalHtml = button.innerHTML;

            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Please wait...';
            hideAlert();

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                const contentType = response.headers.get('content-type') || '';
                let data = {};
                if (contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    throw new Error('Invalid User Code / Register Number.');
                }

                if (!response.ok || data.success !== true) {
                    let errorMessage = data.message || 'Request failed. Please try again.';

                    if (data.errors) {
                        const errors = Object.values(data.errors).flat();
                        if (errors.length) {
                            errorMessage = errors.join(' ');
                        }
                    }

                    throw new Error(errorMessage);
                }

                onSuccess(data);

            } catch (error) {
                showAlert(error.message || 'Unable to connect to the server.');
            } finally {
                button.disabled = false;
                button.innerHTML = originalHtml;
            }
        }

        // STEP 1: Send OTP
        loginForm.addEventListener('submit', function (event) {
            event.preventDefault();

            if (!loginForm.reportValidity()) return;

            submitAjax(
                loginForm,
                document.getElementById('send-otp-btn'),
                function (data) {
                    if (data.redirect) {
                        window.location.assign(data.redirect);
                        return;
                    }

                    loginForm.classList.add('hidden');
                    otpForm.classList.remove('hidden');

                    otpLabel.innerHTML = '<span>' + (data.message || 'Enter the OTP sent to your registered email') + '</span>';
                    otpNote.textContent = 'OTP is valid for 5 minutes.';

                    hideAlert();
                    otpInput.value = '';
                    otpInput.focus();
                }
            );
        });

        // Restrict OTP to 6 digits
        otpInput.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
        });

        // STEP 2: Verify OTP
        otpForm.addEventListener('submit', function (event) {
            event.preventDefault();

            if (!otpForm.reportValidity()) return;

            if (!/^[0-9]{6}$/.test(otpInput.value)) {
                showAlert('Please enter a valid 6-digit OTP.');
                otpInput.focus();
                return;
            }

            submitAjax(
                otpForm,
                document.getElementById('verify-btn'),
                function (data) {
                    if (data.redirect) {
                        window.location.assign(data.redirect);
                        return;
                    }

                    showAlert(data.message || 'Login successful.', 'success');
                }
            );
        });

        // Return to usercode step
        document.getElementById('back-btn').addEventListener('click', function () {
            otpForm.classList.add('hidden');
            loginForm.classList.remove('hidden');
            otpInput.value = '';
            hideAlert();
            usercodeInput.focus();
        });
    </script>
</body>
</html>
