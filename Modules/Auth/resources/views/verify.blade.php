<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP — VIGNAN CDOE</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f3f4f6; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
        .card { background:#fff; padding:40px; border-radius:10px; box-shadow:0 10px 40px rgba(0,0,0,.1); width:400px; text-align:center; }
        h2 { margin:0 0 8px; color:#c1121f; }
        p { color:#555; font-size:14px; }
        input[type=text] { width:100%; padding:14px; font-size:22px; letter-spacing:6px; text-align:center; border:2px solid #ccc; border-radius:6px; margin:20px 0; }
        input[type=text]:focus { border-color:#c1121f; outline:none; }
        button { width:100%; padding:12px; background:#c1121f; color:#fff; border:none; border-radius:4px; font-size:16px; font-weight:600; cursor:pointer; }
        .alert { padding:10px; border-radius:4px; margin-bottom:12px; font-size:14px; }
        .alert-error { background:#fee2e2; color:#991b1b; }
        .alert-success { background:#dcfce7; color:#166534; }
        a { display:inline-block; margin-top:14px; color:#555; font-size:13px; }
    </style>
</head>
<body>
<div class="card">
    <h2>Enter OTP</h2>
    <p>{{ session('success') ?? 'Check your registered email.' }}</p>

    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('verify.otp') }}">
        @csrf
        <input type="text" name="otp" maxlength="6" pattern="\d{6}" placeholder="••••••" required autofocus>
        <button type="submit">Verify & Login</button>
    </form>
    <a href="{{ route('login') }}">← Back to Login</a>
</div>
</body>
</html>
