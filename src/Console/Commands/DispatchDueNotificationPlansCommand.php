<?php

namespace Modules\Glitter\NotificationHub\Console\Commands;

use Illuminate\Console\Command;
use Carbon\CarbonImmutable;
use Modules\Glitter\NotificationHub\Services\DuePlanDispatchService;

class DispatchDueNotificationPlansCommand extends Command
{
    protected $signature = 'notification-hub:dispatch-due {--limit=} {--dry-run}';

    protected $description = 'Dispatch due notification plans';

    public function handle(DuePlanDispatchService $service): int
    {
        $result = $service->dispatchDue(CarbonImmutable::now(), (int) ($this->option('limit') ?: config('notification-hub.claim_batch_size', 50)), (bool) $this->option('dry-run'));
        if ($result->disabled) {
            $this->components->info(__('glitter-notification_hub::messages.dispatch_disabled'));
            return self::SUCCESS;
        }
        $this->components->info(__('glitter-notification_hub::messages.dispatch_summary', $result->toArray()));
        return self::SUCCESS;
    }
}
