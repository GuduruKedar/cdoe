<!DOCTYPE html>
<html>
<head><title>No Role Assigned</title>
    <style>
        body { font-family: Arial, sans-serif; background: #0f172a; color: #e2e8f0;
            display: flex; align-items: center; justify-content: center; min-height: 100vh; margin:0; }
        .card { background:#1f2937; padding: 40px 60px; border-radius: 12px; text-align:center; }
        h1 { color: #f87171; }
        a { display:inline-block; margin-top:16px; color:#10b981; text-decoration:none; font-weight:600; }
    </style>
</head>
<body>
<div class="card">
    <h1>⚠ No Role Assigned</h1>
    <p>Hello <strong>{{ $user->usercode }}</strong>,</p>
    <p>You do not have an active role or menu access. Contact admin.</p>
    <a href="{{ route('logout') }}">Logout</a>
{{--    <a href="{{ route('dashboard') }}">Dashboard</a>--}}
</div>
</body>
</html>
