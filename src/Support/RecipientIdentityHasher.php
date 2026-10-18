<?php

namespace Modules\Glitter\NotificationHub\Support;

use Modules\Glitter\NotificationHub\DTO\NotificationPlanData;

class RecipientIdentityHasher
{
    public function hash(NotificationPlanData $data): string
    {
        $identity = $data->recipientId !== null
            ? 'id:'.$data->recipientId
            : 'hash:'.($data->recipientIdentifierHash ?? 'anonymous');

        return hash('sha256', $data->recipientType.':'.$identity);
    }
}
