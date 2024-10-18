<!DOCTYPE html>
<html>
<head>
    <title>Notes For Therapy: OTP Code</title>
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
    </style>
</head>
<body>

<strong>Dear {{$user->name}},</strong>
<p>Your one-time password (OTP) code is:</p>
<b style="font-size: 36px; font-weight: 600;">{{$otp_code}}</b>
<p>This code will expire in 10 minutes.</p>

<p>
    <br>
    If you did not request this code, please ignore this email.
    <br>
</p>

<p>Thank you for using Notes For Therapy!</p>
<p>Kate Stewart</p>
<p>Creator of Notes for Therapy</p>
<p>Notes For Therapy is sponsored by Modern Therapy Seattle</p>
<img src="{{asset('/storage/picture/appLogo.png')}}"  height="100px" width="100px" alt="Notes For Therapy logo">
</body>
</html>
