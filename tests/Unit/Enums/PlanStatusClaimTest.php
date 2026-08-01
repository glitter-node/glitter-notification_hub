<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\Enums;
use Modules\Glitter\NotificationHub\Enums\PlanStatus;
use PHPUnit\Framework\TestCase;
class PlanStatusClaimTest extends TestCase
{
    public function test_claimed_plan_can_be_skipped_before_an_attempt_is_created(): void
    {
        $this->assertTrue(PlanStatus::Claimed->canTransitionTo(PlanStatus::Skipped));
        $this->assertFalse(PlanStatus::Skipped->canTransitionTo(PlanStatus::Processing));
    }
    public function test_claim_is_required_before_processing(): void { $this->assertTrue(PlanStatus::Pending->canTransitionTo(PlanStatus::Claimed)); $this->assertFalse(PlanStatus::Pending->canTransitionTo(PlanStatus::Processing)); $this->assertTrue(PlanStatus::Claimed->canTransitionTo(PlanStatus::Processing)); }
    public function test_claim_can_only_restore_waiting_states(): void { foreach ([PlanStatus::Pending, PlanStatus::Scheduled, PlanStatus::RetryScheduled] as $status) $this->assertTrue(PlanStatus::Claimed->canTransitionTo($status)); }
}
