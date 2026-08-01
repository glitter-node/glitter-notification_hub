<?php

namespace Modules\Glitter\NotificationHub\Adapters;

use Modules\Glitter\NotificationHub\Contracts\CoreNotificationAdapterInterface;
use Modules\Glitter\NotificationHub\DTO\CoreNotificationDeliveryResultData;
use Modules\Glitter\NotificationHub\Exceptions\ExternalDeliveryDisabledException;
use Modules\Glitter\NotificationHub\DTO\CoreNotificationDeliveryData;

class NullCoreNotificationAdapter implements CoreNotificationAdapterInterface
{
    public function isEnabledFor(string $channel): bool { return false; }
    public function deliver(CoreNotificationDeliveryData $data): CoreNotificationDeliveryResultData
    {
        throw new ExternalDeliveryDisabledException;
    }
}
