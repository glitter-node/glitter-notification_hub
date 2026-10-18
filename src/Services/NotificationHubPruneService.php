<?php

namespace Modules\Glitter\NotificationHub\Services;

use DateTimeInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\AttemptRepositoryInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\DeadLetterRepositoryInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\PlanRepositoryInterface;
use Modules\Glitter\NotificationHub\DTO\PruneNotificationHubResultData;

class NotificationHubPruneService
{
    public function __construct(private readonly PlanRepositoryInterface $plans, private readonly AttemptRepositoryInterface $attempts, private readonly DeadLetterRepositoryInterface $deadLetters) {}

    public function prune(DateTimeInterface $plansBefore, DateTimeInterface $deadLettersBefore, int $limit, bool $dryRun): PruneNotificationHubResultData
    {
        $plans = $this->plans->findPrunablePlans($plansBefore, $limit);
        if ($dryRun) return new PruneNotificationHubResultData($plans->count(), 0, 0, 0, 0);
        $deletedPlans = $deletedAttempts = $deletedDeadLetters = $protected = 0;
        foreach ($plans as $plan) {
            $deadLetter = $this->deadLetters->findByPlanId((string) $plan->getKey());
            if ($deadLetter !== null && $deadLetter->resolved_at === null) { $protected++; continue; }
            if ($deadLetter !== null) { $this->deadLetters->delete($deadLetter); $deletedDeadLetters++; }
            $deletedAttempts += $this->attempts->deleteForPlan($plan);
            $this->plans->delete($plan);
            $deletedPlans++;
        }
        foreach ($this->deadLetters->findResolvedBefore($deadLettersBefore, $limit) as $deadLetter) {
            $this->deadLetters->delete($deadLetter);
            $deletedDeadLetters++;
        }
        return new PruneNotificationHubResultData($plans->count(), $deletedPlans, $deletedAttempts, $deletedDeadLetters, $protected);
    }
}
