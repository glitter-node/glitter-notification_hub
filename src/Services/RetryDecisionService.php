<?php

namespace Modules\Glitter\NotificationHub\Services;

use DateTimeImmutable;
use DateTimeInterface;
use Modules\Glitter\NotificationHub\Contracts\RetryDecisionServiceInterface;
use Modules\Glitter\NotificationHub\DTO\CoreNotificationDeliveryResultData;
use Modules\Glitter\NotificationHub\DTO\RetryDecisionData;
use Modules\Glitter\NotificationHub\Enums\CoreDeliveryOutcome;
use Modules\Glitter\NotificationHub\Enums\RetryPolicy;
use Modules\Glitter\NotificationHub\Models\NotificationDeliveryAttempt;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;

class RetryDecisionService implements RetryDecisionServiceInterface
{
    private const TRANSIENT_PATTERNS = ['timeout', 'connection', 'temporarily', 'try again', 'rate limit', '429', 'server error'];

    public function decide(NotificationPlan $plan, NotificationDeliveryAttempt $attempt, CoreNotificationDeliveryResultData $result, DateTimeInterface $now): RetryDecisionData
    {
        $reason = $result->reasonMessage ?? $result->reasonCode ?? '';
        if (! config('notification-hub.retry_enabled', false)) return $this->terminal('retry_disabled', $reason);
        if ($plan->retry_policy === RetryPolicy::None) return $this->terminal('retry_policy_none', $reason);
        if ($result->outcome !== CoreDeliveryOutcome::Failed) return $this->terminal('outcome_not_retryable', $reason);
        if ($plan->expires_at !== null && $plan->expires_at->lte($now)) return $this->terminal('plan_expired', $reason);
        if ((int) $plan->attempt_count >= (int) $plan->max_attempts) return $this->terminal('retry_exhausted', $reason);
        if (! $this->isTransient($reason)) return $this->terminal('permanent_failure', $reason);

        $backoff = config('notification-hub.retry_backoff.'.$plan->retry_policy->value, []);
        $index = max(0, (int) $attempt->attempt_number - 1);
        if (! array_key_exists($index, $backoff)) return $this->terminal('retry_backoff_exhausted', $reason);

        $next = DateTimeImmutable::createFromInterface($now)->modify('+'.(int) $backoff[$index].' seconds');
        return new RetryDecisionData(true, $next);
    }

    private function isTransient(string $message): bool
    {
        $message = mb_strtolower($message);
        foreach (self::TRANSIENT_PATTERNS as $pattern) {
            if (str_contains($message, $pattern)) return true;
        }
        return false;
    }

    private function terminal(string $code, string $message): RetryDecisionData { return new RetryDecisionData(false, null, $code, $message); }
}
