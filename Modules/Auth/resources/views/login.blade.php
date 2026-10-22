<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login — VIGNAN CDOE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            min-height: 100vh;
        }

        .left {
            flex: 1;
            background: #c1121f;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .image_left {
            width: 100%;
            height: 100vh;
            object-fit: cover;
        }

        .right {
            flex: 1;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
        }

        .wrapper {
            width: 100%;
            max-width: 420px;
        }

        .card {
            width: 100%;
            padding: 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,.08);
        }

        .card h2 {
            margin: 0 0 20px;
            color: #111;
        }

        .card h2 span { color: #c1121f; }

        p {
            color: #555;
            font-size: 14px;
            line-height: 1.6;
        }

        label {
            display: block;
            margin: 16px 0 6px;
            color: #333;
            font-weight: 500;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px 0;
            border: none;
            border-bottom: 2px solid #ccc;
            font-size: 16px;
            outline: none;
            background: transparent;
        }

        input[type="text"]:focus {
            border-bottom-color: #c1121f;
        }

        .otp-input {
            font-size: 24px !important;
            letter-spacing: 8px;
            text-align: center;
        }

        button {
            margin-top: 24px;
            padding: 12px 20px;
            width: 100%;
            background: #c1121f;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover { background: #a10e18; }

        button:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .secondary-btn {
            margin-top: 10px;
            background: #666;
        }

        .secondary-btn:hover { background: #444; }

        .note {
            font-size: 12px;
            color: #666;
            margin-top: 14px;
        }

        .footer {
            text-align: center;
            line-height: 1.6;
        }

        .alert {
            padding: 10px 14px;
            border-radius: 4px;
            margin-bottom: 12px;
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .hidden { display: none !important; }

        @media (max-width: 768px) {
            body { flex-direction: column; }

            .left { flex: none; height: 180px; }

            .image_left { height: 180px; }

            .right { padding: 20px; }

            .card { padding: 28px 24px; }
        }
    </style>
</head>

<body>

<div class="left">
    <img
        class="image_left"
        src="{{ asset('images/login-hero.png') }}"
        alt="VIGNAN CDOE"
    >
</div>

<div class="right">
    <div class="wrapper">

        <div class="card">

            <h2>Login <span>↪</span></h2>

            <p id="login-message">
                Enter your registered
                <strong>User Code / Register Number</strong>
            </p>

            <div
                id="alert-box"
                class="alert hidden"
                role="alert"
                aria-live="polite"
            ></div>

            {{-- STEP 1: USER CODE --}}
            <form
                id="login-form"
                method="POST"
                action="{{ route('send.otp') }}"
            >
                @csrf

                <label for="usercode">
                    User Code / Register Number
                </label>

                <input
                    type="text"
                    id="usercode"
                    name="usercode"
                    placeholder="e.g. 02266"
                    value="{{ old('usercode') }}"
                    autocomplete="username"
                    required
                    autofocus
                >

                <button type="submit" id="send-otp-btn">
                    Get OTP
                </button>
            </form>

            {{-- STEP 2: OTP VERIFICATION --}}
            <form
                id="otp-form"
                method="POST"
                action="{{ route('verify.otp') }}"
                class="hidden"
            >
                @csrf

                <label for="otp">Enter the 6-digit OTP</label>

                <input
                    type="text"
                    id="otp"
                    name="otp"
                    class="otp-input"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    placeholder="••••••"
                    title="Enter the 6-digit OTP"
                    required
                >

                <button type="submit" id="verify-btn">
                    Verify &amp; Login
                </button>

                <button
                    type="button"
                    id="back-btn"
                    class="secondary-btn"
                >
                    Back to Login
                </button>
            </form>

            <div class="note" id="otp-note">
                Demo login mode • OTP valid for 5 minutes
            </div>

        </div>

        <footer class="note footer">
            {{ now()->year }} © Copyright VIGNAN
            (Deemed-to-be-University). All Rights Reserved.
        </footer>

    </div>
</div>

<script>
    const loginForm = document.getElementById('login-form');
    const otpForm = document.getElementById('otp-form');
    const alertBox = document.getElementById('alert-box');
    const message = document.getElementById('login-message');
    const otpInput = document.getElementById('otp');

    function showAlert(text, type = 'error') {
        alertBox.textContent = text;
        alertBox.className = 'alert alert-' + type;
    }

    function hideAlert() {
        alertBox.className = 'alert hidden';
        alertBox.textContent = '';
    }

    async function submitAjax(form, button, onSuccess) {
        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = 'Please wait...';
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

            const data = await response.json();

            if (!response.ok || data.success !== true) {
                let errorMessage =
                    data.message || 'Request failed. Please try again.';

                // Laravel validation errors
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
            showAlert(
                error.message || 'Unable to connect to the server.'
            );
        } finally {
            button.disabled = false;
            button.textContent = originalText;
        }
    }

    // STEP 1: Send OTP without refreshing
    loginForm.addEventListener('submit', function (event) {
        event.preventDefault();

        if (!loginForm.reportValidity()) return;

        submitAjax(
            loginForm,
            document.getElementById('send-otp-btn'),
            function (data) {
                // Development auto-login: go directly to dashboard
                if (data.redirect) {
                    window.location.assign(data.redirect);
                    return;
                }

                // Normal login: show OTP form
                loginForm.classList.add('hidden');
                otpForm.classList.remove('hidden');

                message.textContent = data.message ||
                    'Enter the OTP sent to your registered email.';

                document.getElementById('otp-note').textContent =
                    'OTP is valid for 5 minutes.';

                hideAlert();
                otpInput.value = '';
                otpInput.focus();
            }
        );
    });

    // Allow only digits and a maximum of six characters
    otpInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
    });

    // STEP 2: Validate OTP through Laravel without refreshing
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

                showAlert(
                    data.message || 'Login successful.',
                    'success'
                );
            }
        );
    });

    // Return to the user-code form
    document.getElementById('back-btn').addEventListener('click', function () {
        otpForm.classList.add('hidden');
        loginForm.classList.remove('hidden');

        message.innerHTML =
            'Enter your registered <strong>User Code / Register Number</strong>';

        otpInput.value = '';
        hideAlert();
        document.getElementById('usercode').focus();
    });
</script>

</body>
</html>
