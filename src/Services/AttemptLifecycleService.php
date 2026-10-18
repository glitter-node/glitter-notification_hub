<?php

namespace Modules\Glitter\NotificationHub\Services;

use DateTimeInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\AttemptRepositoryInterface;
use Modules\Glitter\NotificationHub\Enums\AttemptStatus;
use Modules\Glitter\NotificationHub\Enums\PlanStatus;
use Modules\Glitter\NotificationHub\Exceptions\InvalidAttemptStateTransitionException;
use Modules\Glitter\NotificationHub\Exceptions\InvalidPlanStateTransitionException;
use Modules\Glitter\NotificationHub\Models\NotificationDeliveryAttempt;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use Modules\Glitter\NotificationHub\Support\DeliveryMetadataSanitizer;

class AttemptLifecycleService
{
    public function __construct(private readonly AttemptRepositoryInterface $attempts, private readonly DeliveryMetadataSanitizer $sanitizer) {}

    public function start(NotificationPlan $plan, DateTimeInterface $startedAt, ?string $provider = null): NotificationDeliveryAttempt
    {
        if ($plan->status !== PlanStatus::Processing) { throw new InvalidPlanStateTransitionException($plan->status, PlanStatus::Processing); }
        $attempt = $this->attempts->createNextAttempt($plan, AttemptStatus::Pending, $this->sanitizer->sanitizeText($provider, 100));
        return $this->transition($attempt, AttemptStatus::Processing, ['started_at' => $startedAt]);
    }

    public function markAccepted(NotificationDeliveryAttempt $attempt, DateTimeInterface $completedAt, ?string $providerMessageId = null, ?int $responseStatus = null, ?array $responseMetadata = null): NotificationDeliveryAttempt
    {
        return $this->transition($attempt, AttemptStatus::Accepted, ['completed_at' => $completedAt, 'duration_ms' => $this->duration($attempt, $completedAt), 'provider_message_id' => $this->sanitizer->sanitizeText($providerMessageId, 191), 'response_status' => $responseStatus, 'response_metadata' => $responseMetadata === null ? null : $this->sanitizer->sanitize($responseMetadata)]);
    }

    public function markFailed(NotificationDeliveryAttempt $attempt, DateTimeInterface $completedAt, bool $retryable, ?DateTimeInterface $nextRetryAt, ?string $errorCategory, ?string $errorCode, ?string $errorMessage, ?int $responseStatus = null, ?array $responseMetadata = null): NotificationDeliveryAttempt
    {
        return $this->transition($attempt, AttemptStatus::Failed, ['completed_at' => $completedAt, 'duration_ms' => $this->duration($attempt, $completedAt), 'retryable' => $retryable, 'next_retry_at' => $nextRetryAt, 'error_category' => $this->sanitizer->sanitizeText($errorCategory, 50), 'error_code' => $this->sanitizer->sanitizeText($errorCode, 100), 'error_message' => $this->sanitizer->sanitizeText($errorMessage, 1000), 'response_status' => $responseStatus, 'response_metadata' => $responseMetadata === null ? null : $this->sanitizer->sanitize($responseMetadata)]);
    }

    public function cancel(NotificationDeliveryAttempt $attempt, DateTimeInterface $cancelledAt): NotificationDeliveryAttempt { return $this->transition($attempt, AttemptStatus::Cancelled, ['completed_at' => $cancelledAt, 'duration_ms' => $this->duration($attempt, $cancelledAt)]); }

    /** @param array<string, mixed> $attributes */
    private function transition(NotificationDeliveryAttempt $attempt, AttemptStatus $next, array $attributes): NotificationDeliveryAttempt
    {
        $current = $attempt->status;
        if (! $current->canTransitionTo($next)) { throw new InvalidAttemptStateTransitionException($current, $next); }
        return $this->attempts->updateState($attempt, $next, $attributes);
    }

    private function duration(NotificationDeliveryAttempt $attempt, DateTimeInterface $completedAt): int
    {
        if ($attempt->started_at === null) { return 0; }
        return max(0, (int) round(((float) $completedAt->format('U.u') - (float) $attempt->started_at->format('U.u')) * 1000));
    }
}
