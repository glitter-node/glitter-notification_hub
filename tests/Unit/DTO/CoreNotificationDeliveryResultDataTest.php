<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\DTO;

use Modules\Glitter\NotificationHub\DTO\CoreNotificationDeliveryResultData;
use Modules\Glitter\NotificationHub\Enums\CoreDeliveryOutcome;
use PHPUnit\Framework\TestCase;

class CoreNotificationDeliveryResultDataTest extends TestCase
{
    public function test_nullable_provider_fields_are_preserved(): void
    {
        $data = new CoreNotificationDeliveryResultData(CoreDeliveryOutcome::Blocked, null, null, null, null, null, null, null);
        $this->assertSame('blocked', $data->toArray()['outcome']);
        $this->assertNull($data->toArray()['provider_message_id']);
    }
}
