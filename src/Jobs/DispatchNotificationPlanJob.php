<?php

namespace Modules\Glitter\NotificationHub\Jobs;

use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Glitter\NotificationHub\Contracts\Repositories\PlanRepositoryInterface;
use Modules\Glitter\NotificationHub\Services\NotificationDeliveryOrchestrator;

class DispatchNotificationPlanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;
    public int $timeout;

    public function __construct(public readonly string $planId, public readonly string $claimToken)
    {
        $this->tries = (int) config('notification-hub.job_tries', 1);
        $this->timeout = (int) config('notification-hub.job_timeout_seconds', 120);
        $this->afterCommit = true;
    }

    public function handle(PlanRepositoryInterface $plans, NotificationDeliveryOrchestrator $orchestrator): void
    {
        $plan = $plans->consumeClaim($this->planId, $this->claimToken, CarbonImmutable::now());
        if ($plan === null) {
            return;
        }

        $orchestrator->deliverProcessingPlan($plan);
    }
}
