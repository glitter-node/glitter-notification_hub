<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\Enums;

use Modules\Glitter\NotificationHub\Enums\AttemptStatus;
use PHPUnit\Framework\TestCase;

class AttemptStatusTest extends TestCase
{
    public function test_values_match_the_domain_contract(): void
    {
        $this->assertSame(
            ['pending', 'processing', 'accepted', 'failed', 'cancelled'],
            AttemptStatus::values(),
        );
    }
}
