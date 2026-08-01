<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\Services;

use Modules\Glitter\NotificationHub\Contracts\CoreNotificationPolicyInspectorInterface;
use Modules\Glitter\NotificationHub\DTO\CoreNotificationPolicyResultData;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use PHPUnit\Framework\TestCase;

class CoreNotificationPolicyInspectorTest extends TestCase
{
    public function test_contract_exposes_explicit_skip_reason(): void
    {
        $inspector = new class implements CoreNotificationPolicyInspectorInterface {
            public function inspect(NotificationPlan $plan, bool $isGuest): CoreNotificationPolicyResultData
            {
                return new CoreNotificationPolicyResultData(false, 'template_unavailable', 'unavailable');
            }
        };
        $result = $inspector->inspect(new NotificationPlan, false);
        $this->assertFalse($result->allowed);
        $this->assertSame('template_unavailable', $result->reasonCode);
    }
}
