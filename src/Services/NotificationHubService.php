<?php

namespace Modules\Glitter\NotificationHub\Services;

use Carbon\CarbonImmutable;
use Modules\Glitter\NotificationHub\Contracts\NotificationHubInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\PlanRepositoryInterface;
use Modules\Glitter\NotificationHub\DTO\NotificationPlanData;
use Modules\Glitter\NotificationHub\DTO\NotificationResultData;
use Modules\Glitter\NotificationHub\Enums\PlanStatus;
use Modules\Glitter\NotificationHub\Exceptions\NotificationPlanAlreadyTerminalException;
use Modules\Glitter\NotificationHub\Exceptions\NotificationPlanNotFoundException;
use Modules\Glitter\NotificationHub\Exceptions\NotificationPlanProcessingException;

class NotificationHubService implements NotificationHubInterface
{
    public function __construct(private readonly PlanRepositoryInterface $plans, private readonly PlanStateTransitionService $transitions) {}

    public function createPlan(NotificationPlanData $data): NotificationResultData
    {
        $creation = $this->plans->createOrFindDuplicate($data);
        $plan = $creation->plan;

        if (! $creation->isDuplicate) {
            $now = CarbonImmutable::now();
            if ($data->expiresAt !== null && $data->expiresAt <= $now) {
                $plan = $this->transitions->markExpired($plan, $data->expiresAt);
            } elseif ($data->scheduledAt !== null && $data->scheduledAt > $now) {
                $plan = $this->plans->updateState($plan, PlanStatus::Scheduled, ['scheduled_at' => $data->scheduledAt]);
            }
        }

        return new NotificationResultData((string) $plan->getKey(), $plan->correlation_id, $plan->status, $creation->isDuplicate);
    }

    public function cancelPlan(string $planId): NotificationResultData
    {
        $plan = $this->plans->findById($planId);
        if ($plan === null) { throw new NotificationPlanNotFoundException; }
        if ($plan->status === PlanStatus::Processing) { throw new NotificationPlanProcessingException; }
        if ($plan->isTerminal()) { throw new NotificationPlanAlreadyTerminalException; }

        $plan = $this->transitions->markCancelled($plan, CarbonImmutable::now());
        return new NotificationResultData((string) $plan->getKey(), $plan->correlation_id, $plan->status, false);
    }
}
