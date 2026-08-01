<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\DTO;

use Modules\Glitter\NotificationHub\DTO\CoreNotificationDeliveryData;
use PHPUnit\Framework\TestCase;

class CoreNotificationDeliveryDataTest extends TestCase
{
    public function test_runtime_notifiable_is_not_serialized(): void
    {
        $data = new CoreNotificationDeliveryData('p', 'a', 'c', 'type', 'hook', 'module', 'm', 'mail', ['name' => 'x'], new \stdClass);
        $this->assertArrayNotHasKey('notifiable', $data->toArray());
        $this->assertSame('mail', $data->toArray()['channel']);
    }
}
