<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ERP Login — Vignan Online CDOE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brand-crimson: #c1121f;
            --brand-crimson-dark: #8e0b14;
            --brand-crimson-deep: #6a070e;
            --brand-gold-border: rgba(217, 119, 6, 0.32);
            --bg-warm-canvas: radial-gradient(circle at 50% 15%, #fffdfa 0%, #fef7ed 45%, #faede0 100%);
            --text-heading: #1f2937;
            --text-body: #4b5563;
            --text-muted: #6b7280;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
            background: var(--bg-warm-canvas);
            color: var(--text-body);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient glow backdrop effects */
        .ambient-glow-1 {
            position: fixed;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            width: min(650px, 90vw);
            height: 350px;
            background: radial-gradient(circle, rgba(220, 38, 38, 0.12) 0%, rgba(245, 158, 11, 0.06) 50%, transparent 70%);
            filter: blur(50px);
            pointer-events: none;
            z-index: 0;
        }

        .login-page-container {
            width: 100%;
            max-width: 480px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
            margin: auto;
        }

        /* Top Institutional Banner Logo */
        .brand-banner-wrap {
            width: 100%;
            max-width: 460px;
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 16px;
            padding: 12px 18px;
            margin-bottom: 22px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .brand-banner-wrap:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
        }
        .brand-banner-img {
            width: 100%;
            height: auto;
            max-height: 78px;
            object-fit: contain;
            display: block;
        }

        /* Main ERP Login Card */
        .erp-auth-card {
            width: 100%;
            background: #ffffff;
            border-radius: 24px;
            border: 1.5px solid var(--brand-gold-border);
            box-shadow: 0 20px 50px -10px rgba(185, 28, 28, 0.16),
                        0 8px 24px -6px rgba(0, 0, 0, 0.06),
                        0 0 0 1px rgba(255, 255, 255, 0.8) inset;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        /* Red Textured Hero Section (Matches Screenshot 1) */
        .card-hero-header {
            background-color: var(--brand-crimson);
            background-image:
                repeating-linear-gradient(45deg, rgba(255, 255, 255, 0.045) 0px, rgba(255, 255, 255, 0.045) 1.5px, transparent 1.5px, transparent 12px),
                repeating-linear-gradient(-45deg, rgba(255, 255, 255, 0.045) 0px, rgba(255, 255, 255, 0.045) 1.5px, transparent 1.5px, transparent 12px),
                linear-gradient(135deg, #a8101b 0%, #c1121f 48%, #780811 100%);
            padding: 26px 24px 34px;
            color: #ffffff;
            position: relative;
            box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.08);
        }

        /* Secure OTP pill badge */
        .secure-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.32);
            border-radius: 999px;
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 600;
            letter-spacing: 0.2px;
            margin-bottom: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }
        .secure-badge-pill i {
            font-size: 11px;
            color: #fef08a;
        }

        .hero-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: -0.3px;
            color: #ffffff;
            margin: 0 0 4px;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }

        .hero-subtitle {
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.88);
            font-weight: 400;
            margin: 0;
            letter-spacing: 0.1px;
        }

        /* Tab Switcher Capsule Container (Overlapping Hero & Body) */
        .tab-capsule-wrapper {
            padding: 0 20px;
            margin-top: -18px;
            position: relative;
            z-index: 5;
        }

        .tab-capsule {
            display: flex;
            background: #ffffff;
            border-radius: 999px;
            padding: 4px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(226, 232, 240, 0.8);
            position: relative;
        }

        .tab-btn {
            flex: 1;
            padding: 9px 18px;
            border-radius: 999px;
            font-size: 13.5px;
            font-weight: 700;
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            outline: none;
        }

        .tab-btn.active {
            background: linear-gradient(180deg, #d91c2b 0%, #a10e18 100%);
            color: #ffffff;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.4),
                        0 4px 12px rgba(161, 14, 24, 0.35);
        }

        .tab-btn:not(.active):hover {
            color: var(--brand-crimson);
            background: rgba(193, 18, 31, 0.04);
        }

        /* Card Content Area */
        .card-body-section {
            padding: 26px 26px 30px;
        }

        /* Alert notifications */
        .alert-box {
            padding: 12px 14px;
            border-radius: 10px;
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
            margin-bottom: 20px;
        }
        .form-label-title {
            display: block;
            font-size: 13px;
            font-weight: 600;
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
            font-size: 16px;
            pointer-events: none;
            transition: color 0.2s ease;
        }
        .cdoe-input {
            width: 100%;
            height: 46px;
            padding: 10px 14px 10px 42px;
            background: #f9fafb;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14.5px;
            color: #111827;
            font-family: inherit;
            outline: none;
            transition: all 0.2s ease;
        }
        .cdoe-input:focus {
            background: #ffffff;
            border-color: var(--brand-crimson);
            box-shadow: 0 0 0 3px rgba(193, 18, 31, 0.12);
        }
        .cdoe-input:focus ~ .field-icon,
        .input-icon-wrap:focus-within .field-icon {
            color: var(--brand-crimson);
        }
        .cdoe-input::placeholder {
            color: #9ca3af;
            font-size: 13.5px;
        }

        /* 6-digit OTP input */
        .otp-input-field {
            height: 52px;
            font-size: 24px !important;
            letter-spacing: 12px;
            text-align: center;
            padding-left: 24px !important;
            font-weight: 700;
            color: #111827;
            background: #ffffff;
        }

        /* Primary Button (Matches 3D red pill styling) */
        .btn-brand-primary {
            width: 100%;
            padding: 12px 20px;
            background: linear-gradient(180deg, #d91c2b 0%, #a10e18 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 14.5px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.4),
                        0 4px 14px rgba(185, 28, 28, 0.35);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            outline: none;
        }
        .btn-brand-primary:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.4),
                        0 6px 18px rgba(185, 28, 28, 0.4);
        }
        .btn-brand-primary:active {
            transform: translateY(1px);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        .btn-brand-primary:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary-back {
            width: 100%;
            margin-top: 10px;
            padding: 10px 16px;
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

        /* ================= REGISTRATION TAB (Matches Screenshot 1) ================= */
        .register-tab-content {
            animation: fadeIn 0.25s ease-in-out;
        }
        .register-heading {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 23px;
            font-weight: 700;
            color: #6a070e;
            margin-bottom: 6px;
            line-height: 1.25;
        }
        .register-intro {
            font-size: 13px;
            color: #4b5563;
            line-height: 1.55;
            margin-bottom: 18px;
        }

        /* Contact Details Card Box (Exact match to Screenshot 1) */
        .contact-details-card {
            background: #fffdf9;
            border: 1px solid #f3d2a2;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.08);
            position: relative;
        }
        .contact-title {
            font-size: 14px;
            font-weight: 800;
            color: #991b1b;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            letter-spacing: 0.1px;
        }
        .contact-field-row {
            font-size: 13.5px;
            color: #374151;
            margin-bottom: 9px;
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
        }

        /* Footer Copyright */
        .page-footer {
            margin-top: 22px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.6;
            max-width: 440px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hidden { display: none !important; }

        @media (max-width: 520px) {
            body { padding: 16px 12px; }
            .hero-title { font-size: 26px; }
            .card-body-section { padding: 22px 18px 26px; }
            .brand-banner-wrap { padding: 10px 14px; margin-bottom: 16px; }
        }
    </style>
</head>
<body>

    <div class="ambient-glow-1" aria-hidden="true"></div>

    <div class="login-page-container">

        {{-- ================= 1. INSTITUTIONAL HEADER LOGO BANNER (Image 2) ================= --}}
        <header class="brand-banner-wrap">
            <img src="{{ asset('images/vignan-online-banner.png') }}"
                 alt="Vignan's Online — Centre for Distance and Online Education"
                 class="brand-banner-img">
        </header>

        {{-- ================= 2. MAIN ERP LOGIN CARD (Image 1) ================= --}}
        <main class="erp-auth-card">

            {{-- Red Textured Hero Section --}}
            <div class="card-hero-header">
                <div class="secure-badge-pill">
                    <i class="bi bi-lock-fill"></i>
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
                        Login
                    </button>
                    <button type="button"
                            class="tab-btn"
                            id="tab-register-btn"
                            role="tab"
                            aria-selected="false"
                            aria-controls="pane-register"
                            onclick="switchAuthTab('register')">
                        Register
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
                                User Code / Register Number
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

                        <button type="submit" id="send-otp-btn" class="btn-brand-primary">
                            <span>Get OTP</span>
                            <i class="bi bi-arrow-right-short" style="font-size: 19px;"></i>
                        </button>
                    </form>

                    {{-- Step 2: OTP Verification Form --}}
                    <form id="otp-form" method="POST" action="{{ route('verify.otp') }}" class="hidden">
                        @csrf

                        <div class="form-group">
                            <label for="otp" class="form-label-title" id="otp-label">
                                Enter the 6-digit OTP
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
                document.getElementById('usercode').focus();
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

                    otpLabel.textContent = data.message || 'Enter the OTP sent to your registered email';
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
            document.getElementById('usercode').focus();
        });
    </script>
</body>
</html>
