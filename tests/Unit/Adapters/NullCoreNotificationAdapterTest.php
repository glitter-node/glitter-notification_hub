<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\Adapters;

use Modules\Glitter\NotificationHub\Adapters\NullCoreNotificationAdapter;
use Modules\Glitter\NotificationHub\DTO\CoreNotificationDeliveryData;
use Modules\Glitter\NotificationHub\Exceptions\ExternalDeliveryDisabledException;
use PHPUnit\Framework\TestCase;

class NullCoreNotificationAdapterTest extends TestCase
{
    public function test_deliver_always_blocks_without_side_effects(): void
    {
        $adapter = new NullCoreNotificationAdapter;
        $this->assertFalse($adapter->isEnabledFor('mail'));
        $this->expectException(ExternalDeliveryDisabledException::class);
        $adapter->deliver(new CoreNotificationDeliveryData('p', 'a', 'c', 'type', 'hook', 'module', 'm', 'mail', [], new \stdClass));
    }
}
