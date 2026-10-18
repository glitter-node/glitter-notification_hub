<?php

return [
    'enabled' => (bool) env('NOTIFICATION_HUB_ENABLED', false),
    'allow_external_delivery' => (bool) env('NOTIFICATION_HUB_ALLOW_EXTERNAL_DELIVERY', false),
    'allowed_channels' => array_values(array_filter(array_map('trim', explode(',', (string) env('NOTIFICATION_HUB_ALLOWED_CHANNELS', ''))))),
    'scheduler_enabled' => (bool) env('NOTIFICATION_HUB_SCHEDULER_ENABLED', false),
    'queue_dispatch_enabled' => (bool) env('NOTIFICATION_HUB_QUEUE_DISPATCH_ENABLED', false),
    'retry_enabled' => (bool) env('NOTIFICATION_HUB_RETRY_ENABLED', false),
    'dead_letter_enabled' => (bool) env('NOTIFICATION_HUB_DEAD_LETTER_ENABLED', true),
    'claim_batch_size' => (int) env('NOTIFICATION_HUB_CLAIM_BATCH_SIZE', 50),
    'claim_timeout_seconds' => (int) env('NOTIFICATION_HUB_CLAIM_TIMEOUT_SECONDS', 300),
    'job_tries' => (int) env('NOTIFICATION_HUB_JOB_TRIES', 1),
    'job_timeout_seconds' => (int) env('NOTIFICATION_HUB_JOB_TIMEOUT_SECONDS', 120),
    'max_attempts' => (int) env('NOTIFICATION_HUB_MAX_ATTEMPTS', 6),
    'plan_retention_days' => (int) env('NOTIFICATION_HUB_PLAN_RETENTION_DAYS', 90),
    'attempt_retention_days' => (int) env('NOTIFICATION_HUB_ATTEMPT_RETENTION_DAYS', 90),
    'dead_letter_retention_days' => (int) env('NOTIFICATION_HUB_DEAD_LETTER_RETENTION_DAYS', 180),
    'retry_backoff' => [
        'none' => [],
        'standard' => [60, 300, 900, 3600, 21600, 86400],
        'aggressive' => [30, 60, 300, 900, 3600, 21600],
    ],
];
