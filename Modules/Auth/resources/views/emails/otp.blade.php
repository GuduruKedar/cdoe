<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f4f4; padding:20px; }
        .card { max-width:600px; margin:auto; background:#fff; border-radius:6px; overflow:hidden; }
        .header { background:#1e3a8a; color:#fff; padding:20px; text-align:center; font-size:20px; font-weight:bold; }
        .body { padding:30px; color:#333; }
        .otp { font-size:32px; letter-spacing:8px; font-weight:bold; text-align:center;
            background:#eef2ff; border:1px dashed #6366f1; padding:15px; margin:20px 0; color:#1e3a8a; }
        .footer { background:#f9fafb; text-align:center; font-size:12px; color:#666; padding:15px; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">VIGNAN CDOE</div>
    <div class="body">
        <p>Hello,</p>
        <p>Use this OTP to log in:</p>
        <div class="otp">{{ $otp }}</div>
        <p>Valid for <strong>5 minutes</strong>. Do not share with anyone.</p>
    </div>
    <div class="footer">
        © {{ date('Y') }} VIGNAN CDOE. All rights reserved.
    </div>
</div>
</body>
</html>
