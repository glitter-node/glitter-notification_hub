<?php

namespace Modules\Glitter\NotificationHub\Contracts;

interface PlanJobDispatcherInterface
{
    public function isEnabled(): bool;
    public function dispatch(string $planId, string $claimToken): void;
}
