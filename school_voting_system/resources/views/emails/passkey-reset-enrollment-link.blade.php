<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your passkey reset link</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111827;">
    <p>Hi {{ $userName }},</p>

    <p>
        Your administrator generated a passkey reset link for your account. To register a new passkey, please open the link below:
    </p>

    <p style="margin: 16px 0;">
        <a href="{{ $enrollmentUrl }}" style="color: #0ea5e9; word-break: break-all;">{{ $enrollmentUrl }}</a>
    </p>

    <p>
        This link will expire in {{ $expiresInMinutes }} minutes.
    </p>

    @if ($recoveryRequestId)
        <p style="color:#4b5563;">Reference ID: #{{ $recoveryRequestId }}</p>
    @endif

    <p>If you did not request this reset, you can ignore this message.</p>

    <p>Thank you,<br>{{ config('app.name') }}</p>
</body>
</html>

