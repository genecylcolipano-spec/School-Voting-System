<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Complete passkey setup</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111827; line-height: 1.5;">
    <p>Hi <?php echo e($userName); ?>,</p>

    <p>Your School Voting System account has been verified against the official school roster.</p>

    <p>Complete your secure passkey setup within <?php echo e($expiresInHours); ?> hours to activate your account.</p>

    <p style="margin: 24px 0;">
        <a href="<?php echo e($enrollmentUrl); ?>"
           style="display: inline-block; background: #0891b2; color: #ffffff; text-decoration: none; padding: 12px 18px; border-radius: 8px; font-weight: 600;">
            Continue to Passkey Setup
        </a>
    </p>

    <p style="color: #6b7280; font-size: 13px; word-break: break-all;">
        Or open this link: <?php echo e($enrollmentUrl); ?>

    </p>

    <p style="color: #6b7280; font-size: 13px;">
        This one-time link expires in <?php echo e($expiresInHours); ?> hours. If it expires, start Create Account again.
    </p>

    <p>Thank you,<br><?php echo e(config('app.name')); ?></p>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/emails/roster-passkey-enrollment.blade.php ENDPATH**/ ?>