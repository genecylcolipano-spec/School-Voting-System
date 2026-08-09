<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $announcement->title }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111827; line-height: 1.5;">
    <p>Hi {{ $recipientName }},</p>

    <p>A new announcement was published:</p>

    <p style="font-size: 18px; font-weight: 700; margin: 16px 0 8px;">{{ $announcement->title }}</p>

    @if ($announcement->summary)
        <p style="color: #374151;">{{ $announcement->summary }}</p>
    @endif

    <p style="margin: 20px 0;">
        <a href="{{ $announcementUrl }}" style="display: inline-block; background: #1e40af; color: #ffffff; text-decoration: none; padding: 10px 16px; border-radius: 6px;">
            View announcement
        </a>
    </p>

    <p style="color: #6b7280; font-size: 13px; word-break: break-all;">
        Or open: {{ $announcementUrl }}
    </p>

    <p>Thank you,<br>{{ config('app.name') }}</p>
</body>
</html>
