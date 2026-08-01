<?php

namespace Modules\Glitter\NotificationHub\Services;

use DateTimeInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\PlanRepositoryInterface;
use Modules\Glitter\NotificationHub\Enums\PlanStatus;
use Modules\Glitter\NotificationHub\Exceptions\InvalidPlanStateTransitionException;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use Modules\Glitter\NotificationHub\Support\DeliveryMetadataSanitizer;

class PlanStateTransitionService
{
    public function __construct(private readonly PlanRepositoryInterface $plans, private readonly DeliveryMetadataSanitizer $sanitizer) {}

    public function markProcessing(NotificationPlan $plan, DateTimeInterface $startedAt): NotificationPlan { return $this->transition($plan, PlanStatus::Processing, ['processing_started_at' => $startedAt, 'next_attempt_at' => null, 'claimed_from_status' => null, 'claim_token' => null, 'claimed_at' => null]); }
    public function markAccepted(NotificationPlan $plan, DateTimeInterface $acceptedAt): NotificationPlan { return $this->transition($plan, PlanStatus::Accepted, ['accepted_at' => $acceptedAt, 'last_error_code' => null, 'last_error_message' => null]); }
    public function markRetryScheduled(NotificationPlan $plan, DateTimeInterface $nextAttemptAt, ?string $errorCode, ?string $errorMessage): NotificationPlan { return $this->transition($plan, PlanStatus::RetryScheduled, ['next_attempt_at' => $nextAttemptAt, ...$this->errors($errorCode, $errorMessage)]); }
    public function markFailed(NotificationPlan $plan, DateTimeInterface $failedAt, ?string $errorCode, ?string $errorMessage): NotificationPlan { return $this->transition($plan, PlanStatus::Failed, ['failed_at' => $failedAt, ...$this->errors($errorCode, $errorMessage)]); }
    public function markSkipped(NotificationPlan $plan, ?string $reasonCode, ?string $reasonMessage): NotificationPlan { return $this->transition($plan, PlanStatus::Skipped, $this->errors($reasonCode, $reasonMessage)); }
    public function markCancelled(NotificationPlan $plan, DateTimeInterface $cancelledAt, ?string $reason = null, ?int $actorId = null): NotificationPlan { return $this->transition($plan, PlanStatus::Cancelled, ['cancelled_at' => $cancelledAt, 'cancel_reason' => $this->sanitizer->sanitizeText($reason, 500), 'cancelled_by' => $actorId, 'claim_token' => null, 'claimed_at' => null, 'claimed_from_status' => null]); }
    public function markExpired(NotificationPlan $plan, DateTimeInterface $expiredAt): NotificationPlan { return $this->transition($plan, PlanStatus::Expired, ['expires_at' => $expiredAt]); }

    /** @param array<string, mixed> $attributes */
    private function transition(NotificationPlan $plan, PlanStatus $next, array $attributes): NotificationPlan
    {
        $current = $plan->status;
        if (! $current->canTransitionTo($next)) { throw new InvalidPlanStateTransitionException($current, $next); }
        return $this->plans->updateState($plan, $next, $attributes);
    }

    /** @return array{last_error_code: ?string, last_error_message: ?string} */
    private function errors(?string $code, ?string $message): array
    {
        return ['last_error_code' => $this->sanitizer->sanitizeText($code, 100), 'last_error_message' => $this->sanitizer->sanitizeText($message, 1000)];
    }
}
