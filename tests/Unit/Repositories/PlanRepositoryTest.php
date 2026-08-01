<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\Repositories;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Glitter\NotificationHub\DTO\NotificationPlanData;
use Modules\Glitter\NotificationHub\Enums\RetryPolicy;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use Modules\Glitter\NotificationHub\Repositories\PlanRepository;
use Modules\Glitter\NotificationHub\Support\IdempotencyHasher;
use Modules\Glitter\NotificationHub\Support\RecipientIdentityHasher;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('database')]
class PlanRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_find_methods_share_the_unique_identity(): void
    {
        $repository = $this->repository();
        $data = $this->planData();
        $plan = $repository->create($data);

        $this->assertSame($plan->id, $repository->findById($plan->id)?->id);
        $this->assertSame($plan->id, $repository->findByCorrelationId($plan->correlation_id)?->id);
        $this->assertSame($plan->id, $repository->findByIdempotencyIdentity(
            $data->extensionType,
            $data->extensionIdentifier,
            $data->definitionType,
            (new IdempotencyHasher)->hash($data->extensionType, $data->extensionIdentifier, $data->definitionType, $data->idempotencyKey),
            $repository->makeRecipientIdentityHash($data),
            $data->channel,
        )?->id);
    }

    public function test_database_unique_constraint_rejects_duplicate_identity(): void
    {
        $repository = $this->repository();
        $data = $this->planData();
        $repository->create($data);

        $this->expectException(QueryException::class);
        $repository->create($data);
    }

    private function planData(): NotificationPlanData
    {
        return new NotificationPlanData(
            definitionType: 'test.notice', hookPrefix: 'test.notice',
            extensionType: 'module', extensionIdentifier: 'test-module',
            sourceType: 'test', sourceId: '1', recipientType: 'guest',
            recipientId: null, recipientIdentifierHash: str_repeat('b', 64),
            recipientSnapshot: [], channel: 'mail', data: [], context: [],
            policySnapshot: [], idempotencyKey: 'request-1',
            retryPolicy: RetryPolicy::None, maxAttempts: 1,
            scheduledAt: null, expiresAt: null, createdBy: null,
        );
    }

    private function repository(): PlanRepository
    {
        return new PlanRepository(new NotificationPlan, new IdempotencyHasher, new RecipientIdentityHasher);
    }
}
