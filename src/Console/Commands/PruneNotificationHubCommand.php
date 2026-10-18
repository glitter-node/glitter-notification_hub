<?php

namespace Modules\Glitter\NotificationHub\Console\Commands;

use Illuminate\Console\Command;
use Carbon\CarbonImmutable;
use Modules\Glitter\NotificationHub\Services\NotificationHubPruneService;

class PruneNotificationHubCommand extends Command
{
    protected $signature = 'notification-hub:prune {--dry-run} {--plans-days=} {--attempts-days=} {--dead-letters-days=}';

    protected $description = 'Prune Notification Hub records';

    public function handle(NotificationHubPruneService $service): int
    {
        if (! config('notification-hub.scheduler_enabled', false)) {
            $this->components->info(__('glitter-notification_hub::messages.prune_disabled'));
            return self::SUCCESS;
        }
        $now = CarbonImmutable::now();
        $plansDays = max(1, (int) ($this->option('plans-days') ?: config('notification-hub.plan_retention_days', 90)));
        $attemptsDays = max(1, (int) ($this->option('attempts-days') ?: config('notification-hub.attempt_retention_days', 90)));
        $deadDays = max(1, (int) ($this->option('dead-letters-days') ?: config('notification-hub.dead_letter_retention_days', 180)));
        $auditBefore = $now->subDays(max($plansDays, $attemptsDays));
        $result = $service->prune($auditBefore, $now->subDays($deadDays), 100, (bool) $this->option('dry-run'));
        $this->components->info(__('glitter-notification_hub::messages.prune_summary', $result->toArray()));
        return self::SUCCESS;
    }
}
