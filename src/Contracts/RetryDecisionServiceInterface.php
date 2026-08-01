<?php

namespace Modules\Glitter\NotificationHub\Contracts;

use DateTimeInterface;
use Modules\Glitter\NotificationHub\DTO\CoreNotificationDeliveryResultData;
use Modules\Glitter\NotificationHub\DTO\RetryDecisionData;
use Modules\Glitter\NotificationHub\Models\NotificationDeliveryAttempt;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;

interface RetryDecisionServiceInterface
{
    public function decide(NotificationPlan $plan, NotificationDeliveryAttempt $attempt, CoreNotificationDeliveryResultData $result, DateTimeInterface $now): RetryDecisionData;
}
