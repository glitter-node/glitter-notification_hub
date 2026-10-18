<?php

namespace Modules\Glitter\NotificationHub\Services;

use DateTimeInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\DeadLetterRepositoryInterface;
use Modules\Glitter\NotificationHub\Models\NotificationDeadLetter;
use Modules\Glitter\NotificationHub\Models\NotificationDeliveryAttempt;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use Modules\Glitter\NotificationHub\Support\DeliveryMetadataSanitizer;

class DeadLetterService
{
    public function __construct(private readonly DeadLetterRepositoryInterface $deadLetters, private readonly DeliveryMetadataSanitizer $sanitizer) {}

    public function record(NotificationPlan $plan, ?NotificationDeliveryAttempt $attempt, string $reasonCode, ?string $reasonMessage, DateTimeInterface $failedAt, bool $retryExhausted): ?NotificationDeadLetter
    {
        if (! config('notification-hub.dead_letter_enabled', true)) return null;
        return $this->deadLetters->createForFailure($plan, $attempt, (string) $this->sanitizer->sanitizeText($reasonCode, 100), $this->sanitizer->sanitizeText($reasonMessage, 1000), $failedAt, $retryExhausted);
    }
}
