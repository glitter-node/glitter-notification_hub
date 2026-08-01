<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\Services;

use Modules\Glitter\NotificationHub\Contracts\CoreNotificationAdapterInterface;
use Modules\Glitter\NotificationHub\Contracts\CoreNotificationPolicyInspectorInterface;
use Modules\Glitter\NotificationHub\Contracts\PlanRecipientResolverInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\PlanRepositoryInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\DeadLetterRepositoryInterface;
use Modules\Glitter\NotificationHub\Contracts\RetryDecisionServiceInterface;
use Modules\Glitter\NotificationHub\Exceptions\ExternalDeliveryDisabledException;
use Modules\Glitter\NotificationHub\Exceptions\PlanRecipientResolutionException;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use Modules\Glitter\NotificationHub\Services\AttemptLifecycleService;
use Modules\Glitter\NotificationHub\Services\NotificationDeliveryOrchestrator;
use Modules\Glitter\NotificationHub\Services\PlanStateTransitionService;
use Modules\Glitter\NotificationHub\Services\DeadLetterService;
use Modules\Glitter\NotificationHub\Support\DeliveryMetadataSanitizer;
use Modules\Glitter\NotificationHub\DTO\CoreNotificationPolicyResultData;
use Modules\Glitter\NotificationHub\Enums\PlanStatus;
use PHPUnit\Framework\TestCase;

class NotificationDeliveryOrchestratorTest extends TestCase
{
    public function test_policy_denial_skips_without_attempt_or_adapter_delivery(): void
    {
        $plan = new NotificationPlan(['status' => PlanStatus::Processing, 'channel' => 'mail', 'recipient_type' => 'user']);
        $plan->id = 'plan'; $plan->correlation_id = 'correlation';
        $plans = $this->createMock(PlanRepositoryInterface::class);
        $plans->expects($this->once())->method('updateState')->with($plan, PlanStatus::Skipped, $this->isType('array'))->willReturnCallback(function ($model, $status) { $model->status = $status; return $model; });
        $policy = $this->createMock(CoreNotificationPolicyInspectorInterface::class);
        $policy->method('inspect')->willReturn(new CoreNotificationPolicyResultData(false, 'definition_inactive', 'inactive'));
        $attempts = $this->createMock(AttemptLifecycleService::class); $attempts->expects($this->never())->method('start');
        $adapter = $this->createMock(CoreNotificationAdapterInterface::class); $adapter->method('isEnabledFor')->willReturn(true); $adapter->expects($this->never())->method('deliver');
        $service = new NotificationDeliveryOrchestrator($plans, $this->createMock(PlanRecipientResolverInterface::class), $policy, $adapter, new PlanStateTransitionService($plans, new DeliveryMetadataSanitizer), $attempts, $this->createMock(RetryDecisionServiceInterface::class), new DeadLetterService($this->createMock(DeadLetterRepositoryInterface::class), new DeliveryMetadataSanitizer));
        $result = $service->deliverProcessingPlan($plan);
        $this->assertSame(PlanStatus::Skipped, $result->status);
    }

    public function test_missing_recipient_skips_without_attempt_or_adapter_delivery(): void
    {
        $plan = new NotificationPlan(['status' => PlanStatus::Processing, 'channel' => 'mail', 'recipient_type' => 'user']);
        $plan->id = 'plan'; $plan->correlation_id = 'correlation';
        $plans = $this->createMock(PlanRepositoryInterface::class);
        $plans->method('updateState')->willReturnCallback(function ($model, $status) { $model->status = $status; return $model; });
        $policy = $this->createMock(CoreNotificationPolicyInspectorInterface::class);
        $policy->method('inspect')->willReturn(new CoreNotificationPolicyResultData(true));
        $recipient = $this->createMock(PlanRecipientResolverInterface::class);
        $recipient->method('resolve')->willThrowException(new PlanRecipientResolutionException('missing'));
        $attempts = $this->createMock(AttemptLifecycleService::class); $attempts->expects($this->never())->method('start');
        $adapter = $this->createMock(CoreNotificationAdapterInterface::class); $adapter->method('isEnabledFor')->willReturn(true); $adapter->expects($this->never())->method('deliver');
        $service = new NotificationDeliveryOrchestrator($plans, $recipient, $policy, $adapter, new PlanStateTransitionService($plans, new DeliveryMetadataSanitizer), $attempts, $this->createMock(RetryDecisionServiceInterface::class), new DeadLetterService($this->createMock(DeadLetterRepositoryInterface::class), new DeliveryMetadataSanitizer));
        $this->assertSame(PlanStatus::Skipped, $service->deliverProcessingPlan($plan)->status);
    }

    public function test_kill_switch_blocks_before_recipient_policy_or_attempt(): void
    {
        $plan = new NotificationPlan(['status' => 'processing', 'channel' => 'mail']);
        $plan->id = 'plan';
        $plans = $this->createMock(PlanRepositoryInterface::class);
        $recipient = $this->createMock(PlanRecipientResolverInterface::class);
        $recipient->expects($this->never())->method('resolve');
        $policy = $this->createMock(CoreNotificationPolicyInspectorInterface::class);
        $policy->expects($this->never())->method('inspect');
        $adapter = $this->createMock(CoreNotificationAdapterInterface::class);
        $adapter->method('isEnabledFor')->willReturn(false);
        $adapter->expects($this->never())->method('deliver');

        $deadLetters = new DeadLetterService($this->createMock(DeadLetterRepositoryInterface::class), new DeliveryMetadataSanitizer);
        $service = new NotificationDeliveryOrchestrator($plans, $recipient, $policy, $adapter, $this->createMock(PlanStateTransitionService::class), $this->createMock(AttemptLifecycleService::class), $this->createMock(RetryDecisionServiceInterface::class), $deadLetters);
        $this->expectException(ExternalDeliveryDisabledException::class);
        $service->deliverProcessingPlan($plan);
    }
}
