<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\Enums;

use Modules\Glitter\NotificationHub\Enums\CoreDeliveryOutcome;
use PHPUnit\Framework\TestCase;

class CoreDeliveryOutcomeTest extends TestCase
{
    public function test_values_are_stable(): void
    {
        $this->assertSame(['accepted', 'failed', 'skipped', 'blocked'], CoreDeliveryOutcome::values());
    }
}
