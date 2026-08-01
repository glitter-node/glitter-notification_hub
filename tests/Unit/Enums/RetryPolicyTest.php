<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\Enums;

use Modules\Glitter\NotificationHub\Enums\RetryPolicy;
use PHPUnit\Framework\TestCase;

class RetryPolicyTest extends TestCase
{
    public function test_values_match_the_domain_contract(): void
    {
        $this->assertSame(['none', 'standard', 'aggressive'], RetryPolicy::values());
    }
}
