<!DOCTYPE html>
<html>
<head>
    <title>Notes For Therapy: Security Alert</title>
    <style>
        body {
            background-color: #ffffff;
            font-family: sans-serif;
            font-size: 16px;
            line-height: 1.5;
            color: #333333;
        }
        h1 {
            font-size: 24px;
            font-weight: bold;
            margin-top: 0;
        }
        p {
            margin-bottom: 10px;
        }
        img {
            max-width: 100%;
            height: auto;
        }
        .alert {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<h1>Security Alert: Unsuccessful Login Attempts</h1>

<div class="alert">
    <strong>Important Notice:</strong> Multiple unsuccessful authorization attempts have been detected on your Notes For Therapy mobile app.
</div>

<p><strong>Dear {{$user->name}},</strong></p>

<p>We want to inform you that there have been three unsuccessful attempts to unlock the Notes For Therapy mobile app.</p>

<p><strong>Attempt Details:</strong></p>
<ul>
    <li>Date and Time: {{$attemptTime}}</li>
    <li>Device info: {{$deviceInfo}}</li>
</ul>

<p>If you do not recognize this activity, please take immediate action:</p>
<ol>
    <li>Change your Notes For Therapy account password immediately</li>
    <li>Review your account security settings</li>
    <li>Contact our support team for assistance</li>
</ol>

<p>Your account security is our top priority. If you have any concerns or need help, please don't hesitate to reach out to us.</p>

<p>Thank you for using Notes For Therapy!</p>

<p>Kate Stewart</p>
<p>Creator of Notes for Therapy</p>
<p>Notes For Therapy is sponsored by Modern Therapy Seattle</p>

<img src="{{asset('/storage/picture/appLogo.png')}}"  height="100px" width="100px" alt="Notes For Therapy logo">

</body>
</html>
