<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\Support;

use Modules\Glitter\NotificationHub\DTO\NotificationCorrelationData;
use Modules\Glitter\NotificationHub\Enums\CoreDeliveryOutcome;
use Modules\Glitter\NotificationHub\Support\NotificationCorrelationRegistry;
use PHPUnit\Framework\TestCase;

class NotificationCorrelationScopeTest extends TestCase
{
    public function test_matching_hook_is_captured_and_scope_is_cleaned(): void
    {
        $registry = new NotificationCorrelationRegistry;
        $notifiable = new \stdClass;
        $key = $registry->register('N', 'type', 'module', 'm', $notifiable, 'mail', new NotificationCorrelationData('p', 'a', 'c'));
        $registry->capture('core.notification.after_channel_send', 'mail', ['notification_class' => 'N', 'notification_type' => 'type', 'extension_type' => 'module', 'extension_identifier' => 'm', 'notifiable' => $notifiable]);
        $this->assertSame(CoreDeliveryOutcome::Accepted, $registry->result($key)?->outcome);
        $registry->forget($key);
        $this->assertSame(0, $registry->count());
    }
}
