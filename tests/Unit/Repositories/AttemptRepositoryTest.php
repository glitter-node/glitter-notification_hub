<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Glitter\NotificationHub\DTO\NotificationPlanData;
use Modules\Glitter\NotificationHub\Enums\AttemptStatus;
use Modules\Glitter\NotificationHub\Enums\RetryPolicy;
use Modules\Glitter\NotificationHub\Models\NotificationDeliveryAttempt;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use Modules\Glitter\NotificationHub\Repositories\AttemptRepository;
use Modules\Glitter\NotificationHub\Repositories\PlanRepository;
use Tests\TestCase;
use Modules\Glitter\NotificationHub\Support\IdempotencyHasher;
use Modules\Glitter\NotificationHub\Support\RecipientIdentityHasher;
use PHPUnit\Framework\Attributes\Group;

#[Group('database')]
class AttemptRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_find_and_latest_methods_use_attempt_number_order(): void
    {
        $plan = (new PlanRepository(new NotificationPlan, new IdempotencyHasher, new RecipientIdentityHasher))->create($this->planData());
        $repository = new AttemptRepository(new NotificationDeliveryAttempt);
        $first = $repository->createForPlan($plan, 1, AttemptStatus::Failed);
        $second = $repository->createForPlan($plan, 2, AttemptStatus::Pending);

        $this->assertSame($first->id, $repository->findById($first->id)?->id);
        $this->assertSame($second->id, $repository->findLatestForPlan($plan)?->id);
    }

    private function planData(): NotificationPlanData
    {
        return new NotificationPlanData(
            definitionType: 'test.notice', hookPrefix: 'test.notice',
            extensionType: 'module', extensionIdentifier: 'test-module',
            sourceType: null, sourceId: null, recipientType: 'user', recipientId: null,
            recipientIdentifierHash: str_repeat('b', 64), recipientSnapshot: [],
            channel: 'database', data: [], context: [], policySnapshot: [],
            idempotencyKey: 'request-2', retryPolicy: RetryPolicy::None,
            maxAttempts: 1, scheduledAt: null, expiresAt: null, createdBy: null,
        );
    }
}
