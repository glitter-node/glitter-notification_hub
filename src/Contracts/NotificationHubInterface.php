<?php

namespace Modules\Glitter\NotificationHub\Contracts;

use Modules\Glitter\NotificationHub\DTO\NotificationPlanData;
use Modules\Glitter\NotificationHub\DTO\NotificationResultData;

interface NotificationHubInterface
{
    public function createPlan(NotificationPlanData $data): NotificationResultData;

    public function cancelPlan(string $planId): NotificationResultData;
}
