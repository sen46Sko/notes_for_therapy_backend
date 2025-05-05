<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>2FA Verification Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .code-box {
            background-color: #f5f5f5;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #2d3748;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Two-Factor Authentication</h2>
        </div>

        <p>Hi {{ $admin->name }},</p>

        <p>Your verification code is:</p>

        <div class="code-box">
            <div class="code">{{ $code }}</div>
        </div>

        <p>This code will expire in 10 minutes.</p>

        <p>If you didn't request this code, please ignore this email.</p>

        <p>Best regards,<br>
        {{ config('app.name') }} Team</p>

        <div class="footer">
            <p>This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>