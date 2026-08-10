<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enrollment link lifetime (hours)
    |--------------------------------------------------------------------------
    |
    | Public roster registration generates a one-time passkey setup link after
    | successful Confirm & Validate. Default: 24 hours.
    |
    */
    'link_expiration_hours' => (int) env('ENROLLMENT_LINK_EXPIRATION', 24),

    /*
    |--------------------------------------------------------------------------
    | Passkey reset / recovery link lifetime (minutes)
    |--------------------------------------------------------------------------
    |
    | Self-service "Lost your passkey?" links use a hashed single-use token.
    | Default: 30 minutes.
    |
    */
    'reset_link_expiration_minutes' => (int) env('PASSKEY_RESET_LINK_EXPIRATION', 30),

    /*
    |--------------------------------------------------------------------------
    | Failed roster validation attempts
    |--------------------------------------------------------------------------
    */
    'max_validation_attempts' => (int) env('REGISTRATION_MAX_VALIDATION_ATTEMPTS', 3),

    'validation_attempt_decay_minutes' => (int) env('REGISTRATION_VALIDATION_DECAY_MINUTES', 60),

];
