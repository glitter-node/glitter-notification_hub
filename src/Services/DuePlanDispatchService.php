<?php

namespace Modules\Glitter\NotificationHub\Services;

use DateTimeInterface;
use Modules\Glitter\NotificationHub\Contracts\PlanJobDispatcherInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\PlanRepositoryInterface;
use Modules\Glitter\NotificationHub\DTO\DuePlanDispatchResultData;

class DuePlanDispatchService
{
    public function __construct(private readonly PlanRepositoryInterface $plans, private readonly PlanJobDispatcherInterface $dispatcher) {}

    public function dispatchDue(DateTimeInterface $now, int $limit, bool $dryRun = false): DuePlanDispatchResultData
    {
        if (! config('notification-hub.scheduler_enabled', false) || ! $this->dispatcher->isEnabled()) return new DuePlanDispatchResultData(disabled: true);
        $eligible = $this->plans->countDuePlans($now);
        $expired = $this->plans->countExpiredWaitingPlans($now);
        if ($dryRun) return new DuePlanDispatchResultData(eligibleCount: $eligible, expiredCount: $expired);

        $released = $this->plans->releaseExpiredClaims($now, (int) config('notification-hub.claim_timeout_seconds', 300));
        $expired = $this->plans->expireWaitingPlans($now, min(max(1, $limit), (int) config('notification-hub.claim_batch_size', 50)));
        $claimed = $this->plans->claimDuePlans($now, min(max(1, $limit), (int) config('notification-hub.claim_batch_size', 50)), (int) config('notification-hub.claim_timeout_seconds', 300));
        $dispatched = $failed = $releasedAfterFailure = 0;
        foreach ($claimed as $plan) {
            try {
                $this->dispatcher->dispatch((string) $plan->getKey(), (string) $plan->claim_token);
                $dispatched++;
            } catch (\Throwable) {
                $failed++;
                if ($this->plans->releaseClaim($plan, (string) $plan->claim_token)) $releasedAfterFailure++;
            }
        }
        return new DuePlanDispatchResultData($eligible, $claimed->count(), $dispatched, $expired, $released + $releasedAfterFailure, $failed);
    }
}
