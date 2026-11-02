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
            --brand-red: #c1121f;
            --brand-red-hover: #9b111e;
            --brand-red-dark: #8b0000;
            --brand-red-light: #fef2f2;
            --brand-red-border: #fecaca;
            --brand-coral: #fca5a5;
            --text-heading: #0f172a;
            --text-body: #475569;
            --text-muted: #64748b;
        }

        html {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
            height: 100vh;
            max-height: 100vh;
            overflow: hidden;
            background-color: #0b1120;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(10px, 1.8vh, 20px) clamp(12px, 2vw, 32px);
            position: relative;
        }

        /* Animated natural background canvas (No red tint, smooth subtle Ken Burns zoom animation) */
        .bg-animated-canvas {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }
        .bg-image-zoom {
            position: absolute;
            top: -1.5%;
            left: -1.5%;
            width: 103%;
            height: 103%;
            background-image: url('{{ asset("images/u-block.jpg") }}');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            animation: kenBurnsSmooth 24s ease-in-out infinite alternate;
            will-change: transform;
            transform-origin: center center;
        }
        @keyframes kenBurnsSmooth {
            0% {
                transform: scale(1) translate(0, 0);
            }
            50% {
                transform: scale(1.015) translate(-0.8%, -0.5%);
            }
            100% {
                transform: scale(1.025) translate(0.6%, 0.3%);
            }
        }
        .bg-overlay {
            position: absolute;
            inset: 0;
            /* Natural dark vignette: preserves real colors of building & sky without any red wash */
            background:
                linear-gradient(180deg, rgba(15, 23, 42, 0.28) 0%, rgba(15, 23, 42, 0.46) 50%, rgba(15, 23, 42, 0.68) 100%),
                radial-gradient(circle at center, transparent 35%, rgba(15, 23, 42, 0.45) 100%);
        }

        /* Main Container */
        .portal-layout-container {
            width: 100%;
            max-width: 1380px;
            padding: 0 clamp(16px, 2.8vw, 48px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: clamp(24px, 4vw, 56px);
            position: relative;
            z-index: 2;
            margin: auto;
            transform: translateY(clamp(-22px, -2.4vh, -8px));
        }

        /* ================= LEFT COLUMN: HERO & FEATURE CARDS ================= */
        .portal-hero-section {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: clamp(430px, 35vw, 505px);
            padding-top: 0;
            padding-bottom: 0;
            margin-top: -6px;
        }

        .hero-header-block {
            margin-bottom: clamp(8px, 1.2vh, 12px);
            padding-top: 0;
        }

        /* Pill Badge: VFSTR • VIGNAN UNIVERSITY */
        .university-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.32);
            border-radius: 999px;
            padding: clamp(4px, 0.6vh, 6px) clamp(12px, 1.1vw, 16px);
            color: #ffffff;
            font-size: clamp(11px, 0.8vw, 12px);
            font-weight: 700;
            letter-spacing: 0.4px;
            width: fit-content;
            margin-bottom: clamp(6px, 0.8vh, 8px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.20);
        }
        .badge-emblem-icon {
            width: clamp(18px, 1.4vw, 22px);
            height: clamp(18px, 1.4vw, 22px);
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.35));
        }

        /* Hero Main Title - Clean Light Luminous Typography */
        .hero-portal-title {
            font-size: clamp(26px, 2.1vw, 32px);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.5px;
            color: #ffffff;
            margin-bottom: clamp(4px, 0.7vh, 6px);
            text-shadow: 0 3px 14px rgba(0, 0, 0, 0.55);
        }
        .hero-portal-title .highlight-coral {
            color: #fca5a5;
            text-shadow: 0 0 20px rgba(239, 68, 68, 0.45);
        }

        .hero-portal-desc {
            font-size: clamp(11px, 0.8vw, 12px);
            line-height: 1.45;
            color: rgba(255, 255, 255, 0.92);
            max-width: 495px;
            margin-bottom: clamp(8px, 1.2vh, 12px);
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.35);
        }

        /* Feature Cards Stack (Horizontal Bars - Reduced width on right) */
        .features-grid {
            display: flex;
            flex-direction: column;
            gap: clamp(5.5px, 0.82vh, 7.5px);
            width: 100%;
            max-width: 505px;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-radius: 12px;
            padding: clamp(6px, 0.82vh, 7.5px) clamp(10px, 0.9vw, 13px);
            display: flex;
            align-items: center;
            gap: clamp(9px, 0.8vw, 11px);
            border: 1px solid rgba(255, 255, 255, 0.95);
            box-shadow: 0 3px 10px -2px rgba(0, 0, 0, 0.12);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .feature-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px -2px rgba(0, 0, 0, 0.18);
        }

        /* Feature Icon Box */
        .feature-icon {
            width: clamp(32px, 2.2vw, 35px);
            height: clamp(32px, 2.2vw, 35px);
            min-width: clamp(32px, 2.2vw, 35px);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: clamp(14px, 1vw, 16px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
        }
        .bg-icon-red    { background: linear-gradient(135deg, #ef4444, #c1121f); }
        .bg-icon-cyan   { background: linear-gradient(135deg, #06b6d4, #0891b2); }
        .bg-icon-purple { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
        .bg-icon-green  { background: linear-gradient(135deg, #10b981, #059669); }
        .bg-icon-orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .bg-icon-blue   { background: linear-gradient(135deg, #0284c7, #2563eb); }

        .feature-body {
            flex: 1;
            min-width: 0;
        }
        .feature-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 2px;
        }
        .feature-name {
            font-size: clamp(11.5px, 0.8vw, 12.5px);
            font-weight: 700;
            color: #1e293b;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .feature-badge {
            font-size: clamp(9px, 0.65vw, 9.5px);
            font-weight: 700;
            padding: 1.5px 7px;
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
        .badge-red    { background: #fef2f2; color: #991b1b; }
        .badge-red .dot { background: #ef4444; }
        .badge-cyan   { background: #ecfeff; color: #0891b2; }
        .badge-cyan .dot { background: #06b6d4; }
        .badge-purple { background: #f5f3ff; color: #6d28d9; }
        .badge-purple .dot { background: #8b5cf6; }
        .badge-green  { background: #ecfdf5; color: #047857; }
        .badge-green .dot { background: #10b981; }
        .badge-orange { background: #fffbeb; color: #b45309; }
        .badge-orange .dot { background: #f59e0b; }
        .badge-blue   { background: #eff6ff; color: #1d4ed8; }
        .badge-blue .dot { background: #3b82f6; }

        .feature-desc {
            font-size: clamp(10px, 0.72vw, 10.8px);
            line-height: 1.35;
            color: #64748b;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ================= RIGHT COLUMN: AUTH CARD ================= */
        .auth-card-wrapper {
            width: 100%;
            max-width: clamp(350px, 28vw, 418px);
            margin: 0 0 0 auto;
            padding: 6px;
            /* Light red translucent frosted glass theme */
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.22) 0%, rgba(220, 38, 38, 0.16) 60%, rgba(255, 255, 255, 0.14) 100%);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-radius: 24px;
            border: 1.5px solid rgba(254, 202, 202, 0.55);
            box-shadow:
                0 25px 60px -10px rgba(0, 0, 0, 0.60),
                0 0 25px rgba(220, 38, 38, 0.18),
                0 12px 25px -5px rgba(0, 0, 0, 0.35);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1),
                        box-shadow 0.35s ease,
                        border-color 0.35s ease,
                        background 0.35s ease;
            will-change: transform, box-shadow;
        }

        /* Subtle smooth growth and soft light-red glass glow when user enters or focuses the form */
        .auth-card-wrapper:hover,
        .auth-card-wrapper:focus-within {
            transform: translateY(-3px) scale(1.02);
            border-color: rgba(254, 202, 202, 0.85);
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.28) 0%, rgba(220, 38, 38, 0.20) 60%, rgba(255, 255, 255, 0.18) 100%);
            box-shadow:
                0 32px 75px -10px rgba(0, 0, 0, 0.68),
                0 16px 32px -6px rgba(0, 0, 0, 0.40),
                0 0 32px rgba(239, 68, 68, 0.30);
        }

        .auth-card {
            background: #ffffff;
            border-radius: 20px;
            padding: clamp(18px, 2.4vh, 24px) clamp(18px, 1.8vw, 26px) clamp(14px, 2vh, 18px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.90);
            position: relative;
            overflow: hidden;
            transition: box-shadow 0.35s ease;
        }

        .auth-card-wrapper:hover .auth-card,
        .auth-card-wrapper:focus-within .auth-card {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        .auth-card-header {
            text-align: center;
            margin-bottom: 14px;
        }

        .portal-shield-img {
            width: 44px;
            height: 44px;
            object-fit: contain;
            margin: 0 auto 6px;
            display: block;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.08));
        }

        .auth-institution-tag {
            font-size: 10px;
            font-weight: 700;
            color: #b91c1c;
            background: #fef2f2;
            border: 1px solid #fee2e2;
            border-radius: 999px;
            padding: 2.5px 10px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .auth-portal-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.3px;
            margin-bottom: 3px;
            line-height: 1.2;
        }
        .auth-portal-title .portal-red,
        .auth-portal-title .portal-blue {
            color: #c1121f;
        }

        .auth-portal-sub {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
            margin: 0;
        }

        /* Capsule Tab Switcher */
        .auth-nav-tabs {
            display: flex;
            background: #f1f5f9;
            border-radius: 10px;
            padding: 3px;
            margin-bottom: 14px;
            border: 1px solid #e2e8f0;
        }
        .auth-tab-btn {
            flex: 1;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12.5px;
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
        .auth-tab-btn:hover {
            color: #0f172a;
        }
        .auth-tab-btn.active {
            background: #ffffff;
            color: #c1121f;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
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
            margin-bottom: 13px;
        }
        .form-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 5px;
        }
        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrap .field-icon {
            position: absolute;
            left: 12px;
            color: #94a3b8;
            font-size: 16px;
            pointer-events: none;
            transition: color 0.2s ease;
        }
        .portal-input {
            width: 100%;
            height: 42px;
            padding: 8px 12px 8px 38px;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            font-size: 13.5px;
            color: #0f172a;
            font-family: inherit;
            outline: none;
            transition: all 0.2s ease;
        }
        .portal-input:focus {
            border-color: #c1121f;
            box-shadow: 0 0 0 3.5px rgba(193, 18, 31, 0.14);
        }
        .portal-input:focus ~ .field-icon,
        .input-wrap:focus-within .field-icon {
            color: #c1121f;
        }
        .portal-input::placeholder {
            color: #94a3b8;
            font-size: 13px;
        }

        /* 6-digit OTP input */
        .otp-input-field {
            height: 46px;
            font-size: 22px !important;
            letter-spacing: 8px;
            text-align: center;
            padding-left: 14px !important;
            font-weight: 700;
            color: #0f172a !important;
            background: #ffffff !important;
            border: 1.5px solid #cbd5e1 !important;
        }

        /* Primary Submit Button */
        .btn-portal-submit {
            width: 100%;
            height: 42px;
            background: linear-gradient(135deg, #d90429 0%, #c1121f 55%, #9b111e 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(193, 18, 31, 0.35);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            outline: none;
            letter-spacing: 0.2px;
        }
        .btn-portal-submit:hover {
            background: linear-gradient(135deg, #c1121f 0%, #8b0000 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(193, 18, 31, 0.45);
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
            margin-top: 8px;
            height: 38px;
            background: transparent;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 13px;
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
            font-size: 11.5px;
            color: #64748b;
            text-align: center;
            margin-top: 10px;
        }

        /* Register Tab Pane */
        .register-container {
            animation: fadeIn 0.2s ease-in-out;
        }
        .register-title {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 5px;
        }
        .register-text {
            font-size: 12.5px;
            color: #475569;
            line-height: 1.45;
            margin-bottom: 12px;
        }
        .register-contact-box {
            background: #fff8f8;
            border: 1px solid #fee2e2;
            border-left: 3.5px solid #c1121f;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 12px;
        }
        .contact-box-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #991b1b;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .contact-row {
            font-size: 12px;
            color: #334155;
            margin-bottom: 6px;
            display: flex;
            gap: 6px;
        }
        .contact-row:last-child { margin-bottom: 0; }
        .contact-row strong { color: #0f172a; }
        .contact-row a { color: #c1121f; text-decoration: none; font-weight: 600; }
        .contact-row a:hover { text-decoration: underline; color: #9b111e; }

        .btn-return-login {
            width: 100%;
            padding: 8px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #c1121f;
            border-radius: 999px;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-return-login:hover {
            background: #fee2e2;
        }

        /* Card Bottom Copyright */
        .auth-card-footer {
            margin-top: 14px;
            text-align: center;
            font-size: 11px;
            color: #64748b;
            line-height: 1.4;
        }
        .auth-card-footer .powered-by {
            font-size: 10.5px;
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
                display: flex;
                justify-content: center;
                align-items: center;
                margin: auto;
                padding: 0;
                gap: 0;
                max-width: 460px;
                transform: none;
            }
            .auth-card-wrapper {
                max-width: 100%;
                width: 100%;
                margin: 0 auto;
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 16px 14px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .auth-card-wrapper {
                padding: 5px;
                border-radius: 24px;
            }
            .auth-card {
                padding: 24px 18px 18px;
                border-radius: 20px;
            }
            .portal-shield-img {
                width: 44px;
                height: 44px;
            }
            .auth-portal-title {
                font-size: 20px;
            }
            .auth-portal-sub {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

    {{-- Natural Animated Background (Smooth Subtle Ken Burns Zoom, No Red Tint) --}}
    <div class="bg-animated-canvas" aria-hidden="true">
        <div class="bg-image-zoom"></div>
        <div class="bg-overlay"></div>
    </div>

    <div class="portal-layout-container">

        {{-- ================= LEFT HERO & FEATURE CARDS (Online Learning) ================= --}}
        <section class="portal-hero-section">

            <div class="hero-header-block">
                {{-- University Pill Badge --}}
                <div class="university-badge-pill">
                    <img src="{{ asset('images/vignan-emblem.png') }}" alt="Vignan Shield" class="badge-emblem-icon">
                    <span>VFSTR • CDOE Online Learning</span>
                </div>

                {{-- Large Headline --}}
                <h1 class="hero-portal-title">
                    Online Learning <span class="highlight-coral">Portal</span>
                </h1>

                <p class="hero-portal-desc">
                    A unified digital campus platform empowering Online MBA &amp; BBA &amp; MCA &amp; BCA learners with industry-aligned curriculum, interactive live lectures, case studies, and corporate mentor connectivity.
                </p>
            </div>

            {{-- 6 Feature Cards Stack (Horizontal Bars) --}}
            <div class="features-grid">

                {{-- 1. Specializations & Electives --}}
                <div class="feature-card">
                    <div class="feature-icon bg-icon-red">
                        <i class="bi bi-briefcase-fill"></i>
                    </div>
                    <div class="feature-body">
                        <div class="feature-header">
                            <span class="feature-name">Specializations &amp; Electives</span>
                            <span class="feature-badge badge-red">
                                <span class="dot"></span> Online Learning
                            </span>
                        </div>
                        <p class="feature-desc">Dual specializations in Finance, Marketing, HR, Operations &amp; Analytics.</p>
                    </div>
                </div>

                {{-- 2. Digital Study Materials --}}
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
                        <p class="feature-desc">Instant access to case studies, e-books, video lectures &amp; journals.</p>
                    </div>
                </div>

                {{-- 3. Live & Recorded Classes --}}
                <div class="feature-card">
                    <div class="feature-icon bg-icon-purple">
                        <i class="bi bi-play-btn-fill"></i>
                    </div>
                    <div class="feature-body">
                        <div class="feature-header">
                            <span class="feature-name">Live &amp; Recorded Classes</span>
                            <span class="feature-badge badge-purple">
                                <span class="dot"></span> Interactive
                            </span>
                        </div>
                        <p class="feature-desc">Interactive live lectures and 24/7 on-demand recorded video sessions.</p>
                    </div>
                </div>

                {{-- 4. Corporate Mentors & Faculty --}}
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
                        <p class="feature-desc">Connect with seasoned management faculty, industry CXOs &amp; mentors.</p>
                    </div>
                </div>

                {{-- 5. Live Projects & Case Studies --}}
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
                        <p class="feature-desc">Corporate simulations, capstone projects &amp; real-world case studies.</p>
                    </div>
                </div>

                {{-- 6. Examinations & Academic Progress --}}
                <div class="feature-card">
                    <div class="feature-icon bg-icon-blue">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <div class="feature-body">
                        <div class="feature-header">
                            <span class="feature-name">Examinations &amp; Academic Progress</span>
                            <span class="feature-badge badge-blue">
                                <span class="dot"></span> Real-Time
                            </span>
                        </div>
                        <p class="feature-desc">Track semester SGPA/CGPA, continuous internal assessments &amp; exams.</p>
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
                        <span>• VFSTR • ONLINE LEARNING (CDOE)</span>
                    </div>
                    <h2 class="auth-portal-title">
                        Online Learning <span class="portal-red">Portal</span>
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
                            <i class="bi bi-info-circle-fill" style="color: #c1121f;"></i>
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
