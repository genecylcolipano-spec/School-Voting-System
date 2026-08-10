<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Passkey Reset Request</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111827; line-height: 1.5; margin: 0; padding: 24px; background: #f1f5f9;">
    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 28px; border: 1px solid #e2e8f0;">
        <p style="margin: 0 0 4px; font-size: 13px; color: #64748b; letter-spacing: 0.02em;">
            <?php echo e(\App\Support\SchoolBranding::systemName()); ?>

        </p>
        <h1 style="margin: 0 0 16px; font-size: 20px; color: #0f172a;">Passkey Reset Request</h1>

        <p style="margin: 0 0 12px;">Hello <?php echo e($userName); ?>,</p>

        <p style="margin: 0 0 16px;">
            <?php if($selfService ?? true): ?>
                We received a request to reset your passkey. Use the button below to continue to passkey setup.
                After you register a new passkey, your previous passkeys will no longer work.
            <?php else: ?>
                Your administrator generated a passkey reset link for your account. Use the button below to continue to passkey setup.
                After you register a new passkey, your previous passkeys will no longer work.
            <?php endif; ?>
        </p>

        <p style="margin: 20px 0;">
            <a href="<?php echo e($enrollmentUrl); ?>"
               style="display: inline-block; background: #0f172a; color: #ffffff; text-decoration: none; padding: 12px 18px; border-radius: 8px; font-weight: 600; font-size: 14px;">
                Continue to Passkey Setup
            </a>
        </p>

        <p style="margin: 0 0 12px; font-size: 13px; color: #475569;">
            Or copy this link:<br>
            <a href="<?php echo e($enrollmentUrl); ?>" style="color: #0369a1; word-break: break-all;"><?php echo e($enrollmentUrl); ?></a>
        </p>

        <p style="margin: 0 0 12px; font-size: 14px;">
            This link will expire in <?php echo e($expiresInMinutes); ?> minutes.
        </p>

        <p style="margin: 0 0 8px; font-size: 13px; color: #64748b;">
            If you did not request this reset, you can safely ignore this email.
        </p>
        <p style="margin: 0; font-size: 13px; color: #64748b;">
            Do not share this link with anyone.
        </p>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/emails/passkey-reset-enrollment-link.blade.php ENDPATH**/ ?>