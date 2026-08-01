<?php
namespace Modules\Glitter\NotificationHub\Contracts;
use Modules\Glitter\NotificationHub\DTO\CoreNotificationPolicyResultData;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
interface CoreNotificationPolicyInspectorInterface { public function inspect(NotificationPlan $plan, bool $isGuest): CoreNotificationPolicyResultData; }
