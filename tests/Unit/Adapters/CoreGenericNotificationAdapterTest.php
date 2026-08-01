<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\Adapters;

use Modules\Glitter\NotificationHub\Adapters\CoreGenericNotificationAdapter;
use Modules\Glitter\NotificationHub\Support\NotificationCorrelationRegistry;
use PHPUnit\Framework\TestCase;

class CoreGenericNotificationAdapterTest extends TestCase
{
    public function test_empty_default_allowlist_blocks_every_channel(): void
    {
        config()->set('notification-hub', ['enabled' => false, 'allow_external_delivery' => false, 'allowed_channels' => []]);
        $adapter = new CoreGenericNotificationAdapter(new NotificationCorrelationRegistry);
        $this->assertFalse($adapter->isEnabledFor('mail'));
        $this->assertFalse($adapter->isEnabledFor('database'));
    }
}
