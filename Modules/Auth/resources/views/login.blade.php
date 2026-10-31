<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ERP Portal Login — Vignan Online CDOE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brand-crimson: #c1121f;
            --brand-crimson-dark: #8e0b14;
            --brand-crimson-deep: #5e060d;
            --brand-crimson-light: #fef2f2;
            --brand-gold: #d97706;
            --brand-gold-soft: #f59e0b;
            --brand-gold-border: rgba(217, 119, 6, 0.28);
            --bg-canvas: #faf6f0;
            --text-heading: #111827;
            --text-body: #4b5563;
            --text-muted: #6b7280;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
            background: var(--bg-canvas);
            background-image:
                radial-gradient(at 0% 0%, rgba(193, 18, 31, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(217, 119, 6, 0.09) 0px, transparent 50%),
                radial-gradient(at 50% 100%, rgba(193, 18, 31, 0.06) 0px, transparent 60%),
                radial-gradient(circle at 50% 50%, #fffefc 0%, #fbf5ec 70%, #f6ebdc 100%);
            background-attachment: fixed;
            color: var(--text-body);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 16px;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Dynamic Glow Orbs */
        .ambient-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(75px);
            pointer-events: none;
            z-index: 0;
            opacity: 0.65;
            transition: all 0.5s ease;
        }
        .ambient-orb-top {
            top: -120px;
            left: 50%;
            transform: translateX(-50%);
            width: min(600px, 90vw);
            height: 380px;
            background: radial-gradient(circle, rgba(193, 18, 31, 0.22) 0%, rgba(245, 158, 11, 0.12) 50%, transparent 75%);
        }
        .ambient-orb-bottom {
            bottom: -150px;
            right: 10%;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(217, 119, 6, 0.15) 0%, rgba(193, 18, 31, 0.08) 50%, transparent 75%);
        }

        /* Watermark Crest Background */
        .background-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: min(650px, 85vw);
            height: min(650px, 85vw);
            background-image: url('{{ asset("images/vignan-emblem.png") }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            opacity: 0.035;
            pointer-events: none;
            z-index: 0;
            filter: grayscale(1);
        }

        .login-page-container {
            width: 100%;
            max-width: 460px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            margin: auto;
        }

        /* Institutional Banner Header */
        .brand-banner-card {
            width: 100%;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 12px 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.06),
                        0 2px 6px rgba(0, 0, 0, 0.02),
                        0 0 0 1px rgba(217, 119, 6, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.28s ease;
        }
        .brand-banner-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #c1121f 0%, #d97706 50%, #2563eb 100%);
        }
        .brand-banner-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.08),
                        0 0 0 1px rgba(217, 119, 6, 0.25);
        }
        .brand-banner-img {
            width: 100%;
            height: auto;
            max-height: 74px;
            object-fit: contain;
            display: block;
        }

        /* Luxury ERP Auth Card */
        .erp-auth-card {
            width: 100%;
            background: #ffffff;
            border-radius: 28px;
            border: 1.5px solid rgba(217, 119, 6, 0.35);
            box-shadow:
                0 30px 70px -15px rgba(185, 28, 28, 0.20),
                0 15px 35px -8px rgba(0, 0, 0, 0.08),
                0 0 0 1px rgba(255, 255, 255, 0.9) inset;
            overflow: hidden;
            position: relative;
            transition: box-shadow 0.3s ease;
        }

        /* Red Textured Hero Header (Screenshot 1 Authentic Design) */
        .card-hero-header {
            background-color: var(--brand-crimson);
            background-image:
                repeating-linear-gradient(45deg, rgba(255, 255, 255, 0.045) 0px, rgba(255, 255, 255, 0.045) 1.5px, transparent 1.5px, transparent 13px),
                repeating-linear-gradient(-45deg, rgba(255, 255, 255, 0.045) 0px, rgba(255, 255, 255, 0.045) 1.5px, transparent 1.5px, transparent 13px),
                linear-gradient(135deg, #7c0710 0%, #b3101b 46%, #d81c2b 85%, #60040a 100%);
            padding: 28px 24px 38px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        /* Shimmering Top Light Streak */
        .card-hero-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: -50%;
            width: 200%;
            height: 100%;
            background: linear-gradient(60deg, transparent 40%, rgba(255, 255, 255, 0.08) 50%, transparent 60%);
            pointer-events: none;
        }

        /* Secure OTP pill badge */
        .secure-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 13px;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.38);
            border-radius: 999px;
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }
        .secure-badge-pill i {
            font-size: 11px;
            color: #fef08a;
        }
        .live-dot {
            width: 6px;
            height: 6px;
            background: #4ade80;
            border-radius: 50%;
            box-shadow: 0 0 6px #4ade80;
            display: inline-block;
        }

        .hero-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -0.4px;
            color: #ffffff;
            margin: 0 0 4px;
            line-height: 1.15;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
        }

        .hero-subtitle {
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.92);
            font-weight: 400;
            margin: 0;
            letter-spacing: 0.15px;
        }

        /* Tab Switcher Capsule Container */
        .tab-capsule-wrapper {
            padding: 0 20px;
            margin-top: -20px;
            position: relative;
            z-index: 5;
        }

        .tab-capsule {
            display: flex;
            background: #ffffff;
            border-radius: 999px;
            padding: 5px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.10), 0 2px 6px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(226, 232, 240, 0.9);
            position: relative;
        }

        .tab-btn {
            flex: 1;
            padding: 10px 20px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 700;
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            outline: none;
        }

        /* 3D Glossy Crimson Pill (Exact match to Screenshot 1) */
        .tab-btn.active {
            background: linear-gradient(180deg, #d91c2b 0%, #9e0e18 100%);
            color: #ffffff;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.5),
                        0 4px 14px rgba(161, 14, 24, 0.38);
        }

        .tab-btn:not(.active):hover {
            color: var(--brand-crimson);
            background: rgba(193, 18, 31, 0.05);
        }

        /* Card Body Content Area */
        .card-body-section {
            padding: 26px 26px 30px;
        }

        /* Notifications Alert Box */
        .alert-box {
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 18px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
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
        .alert-box.hidden {
            display: none !important;
        }

        /* Form Labels & Inputs */
        .form-group {
            margin-bottom: 18px;
        }
        .form-label-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13.5px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 8px;
        }
        .input-icon-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-icon-wrap .field-icon {
            position: absolute;
            left: 14px;
            color: #9ca3af;
            font-size: 17px;
            pointer-events: none;
            transition: color 0.2s ease, transform 0.2s ease;
        }
        .cdoe-input {
            width: 100%;
            height: 48px;
            padding: 10px 14px 10px 44px;
            background: #f9fafb;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14.5px;
            color: #111827;
            font-family: inherit;
            outline: none;
            transition: all 0.22s ease;
        }
        .cdoe-input:focus {
            background: #ffffff;
            border-color: var(--brand-crimson);
            box-shadow: 0 0 0 3.5px rgba(193, 18, 31, 0.14);
        }
        .cdoe-input:focus ~ .field-icon,
        .input-icon-wrap:focus-within .field-icon {
            color: var(--brand-crimson);
            transform: scale(1.08);
        }
        .cdoe-input::placeholder {
            color: #9ca3af;
            font-size: 13.5px;
        }

        /* 6-digit OTP input */
        .otp-input-field {
            height: 54px;
            font-size: 26px !important;
            letter-spacing: 12px;
            text-align: center;
            padding-left: 24px !important;
            font-weight: 700;
            color: #111827;
            background: #ffffff;
            border-color: #cbd5e1;
        }

        /* Quick Demo Account Selector (High Usability) */
        .demo-accounts-pill-row {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .demo-label {
            font-size: 11.5px;
            color: #6b7280;
            font-weight: 600;
        }
        .demo-chip {
            padding: 3px 9px;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 11.5px;
            color: #374151;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            font-family: inherit;
        }
        .demo-chip:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: var(--brand-crimson);
            transform: translateY(-1px);
        }

        /* Primary Action Button (Matches 3D Pill Design) */
        .btn-brand-primary {
            width: 100%;
            height: 48px;
            background: linear-gradient(180deg, #d91c2b 0%, #a10e18 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.45),
                        0 5px 16px rgba(185, 28, 28, 0.38);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            outline: none;
            letter-spacing: 0.2px;
        }
        .btn-brand-primary:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.45),
                        0 8px 22px rgba(185, 28, 28, 0.44);
        }
        .btn-brand-primary:active {
            transform: translateY(1px);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .btn-brand-primary:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary-back {
            width: 100%;
            margin-top: 10px;
            height: 42px;
            background: transparent;
            color: #4b5563;
            border: 1px solid #d1d5db;
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
        .btn-secondary-back:hover {
            background: #f3f4f6;
            color: #111827;
        }

        .helper-text-note {
            font-size: 12px;
            color: #6b7280;
            text-align: center;
            margin-top: 14px;
        }

        /* ================= REGISTRATION TAB (Screenshot 1 Authentic Layout) ================= */
        .register-tab-content {
            animation: fadeIn 0.25s ease-in-out;
        }
        .register-heading {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 24px;
            font-weight: 700;
            color: #5e060d;
            margin-bottom: 6px;
            line-height: 1.25;
        }
        .register-intro {
            font-size: 13.5px;
            color: #4b5563;
            line-height: 1.55;
            margin-bottom: 18px;
        }

        /* Contact Details Card Box (Exact match to Screenshot 1) */
        .contact-details-card {
            background: #fffdf9;
            border: 1px solid #f3d2a2;
            border-left: 3.5px solid var(--brand-crimson);
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.08);
            position: relative;
        }
        .contact-title {
            font-size: 14px;
            font-weight: 800;
            color: #991b1b;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 7px;
            letter-spacing: 0.1px;
        }
        .contact-field-row {
            font-size: 13.5px;
            color: #374151;
            margin-bottom: 10px;
            line-height: 1.5;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .contact-field-row:last-child {
            margin-bottom: 0;
        }
        .contact-field-row strong {
            color: #111827;
            font-weight: 700;
            min-width: 52px;
        }
        .contact-field-row a {
            color: var(--brand-crimson);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.15s ease, text-decoration 0.15s ease;
            word-break: break-all;
        }
        .contact-field-row a:hover {
            color: var(--brand-crimson-dark);
            text-decoration: underline;
        }

        .switch-to-login-btn {
            width: 100%;
            margin-top: 18px;
            padding: 11px 16px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: var(--brand-crimson);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .switch-to-login-btn:hover {
            background: #fee2e2;
            color: var(--brand-crimson-dark);
            transform: translateY(-1px);
        }

        /* Footer Copyright & Trust Marks */
        .page-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 12px;
            color: #8c827a;
            line-height: 1.6;
            max-width: 440px;
        }
        .footer-trust-pills {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
            font-size: 11px;
            color: #9c8e82;
            font-weight: 600;
        }
        .footer-trust-pills span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hidden { display: none !important; }

        @media (max-width: 520px) {
            body { padding: 18px 12px; }
            .hero-title { font-size: 27px; }
            .card-body-section { padding: 22px 18px 26px; }
            .brand-banner-card { padding: 10px 14px; margin-bottom: 16px; }
        }
    </style>
</head>
<body>

    {{-- Ambient Lighting Effects --}}
    <div class="ambient-orb ambient-orb-top" aria-hidden="true"></div>
    <div class="ambient-orb ambient-orb-bottom" aria-hidden="true"></div>
    <div class="background-watermark" aria-hidden="true"></div>

    <div class="login-page-container">

        {{-- ================= 1. INSTITUTIONAL HEADER LOGO BANNER (Image 2) ================= --}}
        <header class="brand-banner-card">
            <img src="{{ asset('images/vignan-online-banner.png') }}"
                 alt="Vignan's Online — Centre for Distance and Online Education"
                 class="brand-banner-img">
        </header>

        {{-- ================= 2. MAIN ERP LOGIN CARD (Image 1) ================= --}}
        <main class="erp-auth-card">

            {{-- Red Textured Hero Section --}}
            <div class="card-hero-header">
                <div class="secure-badge-pill">
                    <span class="live-dot"></span>
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>Secure OTP sign-in</span>
                </div>
                <h1 class="hero-title">ERP Login</h1>
                <p class="hero-subtitle">For students and staff of CDOE</p>
            </div>

            {{-- Floating Tab Switcher Capsule (Login / Register) --}}
            <div class="tab-capsule-wrapper">
                <div class="tab-capsule" role="tablist" aria-label="Sign In or Registration">
                    <button type="button"
                            class="tab-btn active"
                            id="tab-login-btn"
                            role="tab"
                            aria-selected="true"
                            aria-controls="pane-login"
                            onclick="switchAuthTab('login')">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Login</span>
                    </button>
                    <button type="button"
                            class="tab-btn"
                            id="tab-register-btn"
                            role="tab"
                            aria-selected="false"
                            aria-controls="pane-register"
                            onclick="switchAuthTab('register')">
                        <i class="bi bi-person-lines-fill"></i>
                        <span>Register</span>
                    </button>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="card-body-section">

                {{-- Alert Notification Box --}}
                <div id="alert-box"
                     class="alert-box alert-error hidden"
                     role="alert"
                     aria-live="polite">
                    <i class="bi bi-exclamation-circle-fill" id="alert-icon"></i>
                    <span id="alert-text"></span>
                </div>

                {{-- ================= TAB PANE 1: LOGIN ================= --}}
                <div id="pane-login" role="tabpanel" aria-labelledby="tab-login-btn">

                    {{-- Step 1: User Code / Register Number Form --}}
                    <form id="login-form" method="POST" action="{{ route('send.otp') }}">
                        @csrf

                        <div class="form-group">
                            <label for="usercode" class="form-label-title">
                                <span>User Code / Register Number</span>
                            </label>
                            <div class="input-icon-wrap">
                                <i class="bi bi-person-badge field-icon"></i>
                                <input type="text"
                                       id="usercode"
                                       name="usercode"
                                       class="cdoe-input"
                                       placeholder="e.g. 02598 or student register no"
                                       value="{{ old('usercode') }}"
                                       autocomplete="username"
                                       required
                                       autofocus>
                            </div>
                        </div>

                        {{-- Quick Account Shortcuts for Testing --}}
                        <div class="demo-accounts-pill-row">
                            <span class="demo-label">Quick fill:</span>
                            <button type="button" class="demo-chip" onclick="fillCode('02598')">
                                <i class="bi bi-shield-check text-danger me-1"></i>02598 (Admin)
                            </button>
                            <button type="button" class="demo-chip" onclick="fillCode('261CK01001')">
                                <i class="bi bi-mortarboard text-primary me-1"></i>261CK01001 (Student)
                            </button>
                        </div>

                        <button type="submit" id="send-otp-btn" class="btn-brand-primary">
                            <span>Get OTP</span>
                            <i class="bi bi-arrow-right-short" style="font-size: 21px; margin-left: -2px;"></i>
                        </button>
                    </form>

                    {{-- Step 2: OTP Verification Form --}}
                    <form id="otp-form" method="POST" action="{{ route('verify.otp') }}" class="hidden">
                        @csrf

                        <div class="form-group">
                            <label for="otp" class="form-label-title" id="otp-label">
                                <span>Enter the 6-digit OTP</span>
                            </label>
                            <div class="input-icon-wrap">
                                <input type="text"
                                       id="otp"
                                       name="otp"
                                       class="cdoe-input otp-input-field"
                                       maxlength="6"
                                       pattern="[0-9]{6}"
                                       inputmode="numeric"
                                       autocomplete="one-time-code"
                                       placeholder="••••••"
                                       title="Enter the 6-digit OTP"
                                       required>
                            </div>
                        </div>

                        <button type="submit" id="verify-btn" class="btn-brand-primary">
                            <span>Verify &amp; Login</span>
                            <i class="bi bi-check2-circle"></i>
                        </button>

                        <button type="button" id="back-btn" class="btn-secondary-back">
                            <i class="bi bi-arrow-left"></i>
                            <span>Back to Login</span>
                        </button>
                    </form>

                    <div class="helper-text-note" id="otp-note">
                        Demo login mode • OTP valid for 5 minutes
                    </div>

                </div>

                {{-- ================= TAB PANE 2: REGISTER (Matches Screenshot 1) ================= --}}
                <div id="pane-register" class="register-tab-content hidden" role="tabpanel" aria-labelledby="tab-register-btn">
                    <h2 class="register-heading">Registration</h2>
                    <p class="register-intro">
                        New user registration is currently managed by the administration.
                    </p>

                    <div class="contact-details-card">
                        <div class="contact-title">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>Contact details</span>
                        </div>

                        <div class="contact-field-row">
                            <strong>Name:</strong>
                            <span>Mr. D. V. N. S. M. Karthik</span>
                        </div>

                        <div class="contact-field-row">
                            <strong>Email:</strong>
                            <a href="mailto:dmk_cdoe@vignan.ac.in">dmk_cdoe@vignan.ac.in</a>
                        </div>

                        <div class="contact-field-row">
                            <strong>Phone:</strong>
                            <a href="tel:8886852292">8886852292</a>
                        </div>
                    </div>

                    <button type="button" class="switch-to-login-btn" onclick="switchAuthTab('login')">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Already registered? Return to Login</span>
                    </button>
                </div>

            </div>
        </main>

        {{-- ================= 3. FOOTER ================= --}}
        <footer class="page-footer">
            {{ now()->year }} © Vignan's Foundation for Science, Technology &amp; Research
            <br>
            Centre for Distance and Online Education (CDOE). All Rights Reserved.

            <div class="footer-trust-pills">
                <span><i class="bi bi-award-fill text-warning"></i> NAAC A++</span>
                <span>•</span>
                <span><i class="bi bi-check-circle-fill text-success"></i> UGC Recognized</span>
                <span>•</span>
                <span><i class="bi bi-shield-check text-primary"></i> ISO 9001</span>
            </div>
        </footer>

    </div>

    {{-- ================= 4. JAVASCRIPT CONTROLLERS ================= --}}
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

        // Tab Switching function
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
