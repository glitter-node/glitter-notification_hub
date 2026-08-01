<?php

namespace Modules\Glitter\NotificationHub\Contracts;

use Modules\Glitter\NotificationHub\DTO\CoreNotificationDeliveryResultData;
use Modules\Glitter\NotificationHub\DTO\CoreNotificationDeliveryData;

interface CoreNotificationAdapterInterface
{
    public function isEnabledFor(string $channel): bool;
    public function deliver(CoreNotificationDeliveryData $data): CoreNotificationDeliveryResultData;
}
