<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $notification->title }}</title>
</head>
<body>
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
        <h1 style="color: #0088ff;">{{ $notification->title }}</h1>
        <p style="font-size: 16px; line-height: 1.5;">{{ $notification->content }}</p>
        <p style="margin-top: 30px; font-size: 14px; color: #666;">This notification was sent at {{ $notification->created_at->format('Y-m-d H:i:s') }}</p>
        <p><a href="{{ url('/admin/notifications') }}" style="color: #0088ff; text-decoration: none;">View all notifications</a></p>
    </div>
</body>
</html>