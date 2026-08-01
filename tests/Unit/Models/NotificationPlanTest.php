<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\Models;

use Modules\Glitter\NotificationHub\Enums\PlanStatus;
use Modules\Glitter\NotificationHub\Enums\RetryPolicy;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use PHPUnit\Framework\TestCase;

class NotificationPlanTest extends TestCase
{
    public function test_model_metadata_casts_and_relationship_are_declared(): void
    {
        $plan = new NotificationPlan;

        $this->assertSame('notification_hub_plans', $plan->getTable());
        $this->assertFalse($plan->getIncrementing());
        $this->assertSame('string', $plan->getKeyType());
        $this->assertSame(PlanStatus::class, $plan->getCasts()['status']);
        $this->assertSame(RetryPolicy::class, $plan->getCasts()['retry_policy']);
        $this->assertSame('array', $plan->getCasts()['policy_snapshot']);
        $this->assertSame(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            (new \ReflectionMethod($plan, 'attempts'))->getReturnType()?->getName(),
        );
        $this->assertContains('idempotency_hash', $plan->getHidden());
    }

    public function test_terminal_status_helper_has_no_transition_side_effects(): void
    {
        $plan = new NotificationPlan(['status' => PlanStatus::Accepted]);
        $this->assertTrue($plan->isTerminal());

        $plan->status = PlanStatus::Processing;
        $this->assertFalse($plan->isTerminal());
    }
}
