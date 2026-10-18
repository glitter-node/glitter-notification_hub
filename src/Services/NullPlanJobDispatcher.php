<?php

namespace Modules\Glitter\NotificationHub\Services;

use LogicException;
use Modules\Glitter\NotificationHub\Contracts\PlanJobDispatcherInterface;

class NullPlanJobDispatcher implements PlanJobDispatcherInterface
{
    public function isEnabled(): bool { return false; }
    public function dispatch(string $planId, string $claimToken): void { throw new LogicException(__('glitter-notification_hub::exceptions.queue_dispatch_disabled')); }
}
