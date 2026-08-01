<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\Enums;

use Modules\Glitter\NotificationHub\Enums\PlanStatus;
use PHPUnit\Framework\TestCase;

class PlanStatusTest extends TestCase
{
    public function test_values_match_the_domain_contract(): void
    {
        $this->assertSame([
            'pending', 'scheduled', 'claimed', 'processing', 'accepted', 'retry_scheduled',
            'skipped', 'failed', 'cancelled', 'expired',
        ], PlanStatus::values());
    }
}
