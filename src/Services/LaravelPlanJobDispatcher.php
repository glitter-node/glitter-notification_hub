<?php

namespace Modules\Glitter\NotificationHub\Services;

use Modules\Glitter\NotificationHub\Contracts\PlanJobDispatcherInterface;
use Modules\Glitter\NotificationHub\Jobs\DispatchNotificationPlanJob;

class LaravelPlanJobDispatcher implements PlanJobDispatcherInterface
{
    public function isEnabled(): bool { return (bool) config('notification-hub.queue_dispatch_enabled', false); }
    public function dispatch(string $planId, string $claimToken): void
    {
        DispatchNotificationPlanJob::dispatch($planId, $claimToken)->onQueue((string) config('notification-hub.queue', 'notification-hub'));
    }
}
