<?php

namespace Modules\Glitter\NotificationHub\Contracts\Repositories;

use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Glitter\NotificationHub\DTO\Admin\NotificationPlanSearchData;
use Modules\Glitter\NotificationHub\DTO\NotificationPlanData;
use Modules\Glitter\NotificationHub\DTO\PlanCreationResultData;
use Modules\Glitter\NotificationHub\Enums\PlanStatus;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;

interface PlanRepositoryInterface
{
    public function create(NotificationPlanData $data): NotificationPlan;

    public function createOrFindDuplicate(NotificationPlanData $data): PlanCreationResultData;

    public function findById(string $id): ?NotificationPlan;

    public function findByCorrelationId(string $correlationId): ?NotificationPlan;

    public function findByIdempotencyIdentity(
        string $extensionType,
        string $extensionIdentifier,
        string $definitionType,
        string $idempotencyHash,
        string $recipientIdentityHash,
        string $channel,
    ): ?NotificationPlan;

    public function makeRecipientIdentityHash(NotificationPlanData $data): string;

    /** @param array<string, mixed> $attributes */
    public function updateState(NotificationPlan $plan, PlanStatus $status, array $attributes = []): NotificationPlan;

    public function incrementAttemptCount(NotificationPlan $plan): NotificationPlan;

    public function countDuePlans(DateTimeInterface $now): int;
    public function countExpiredWaitingPlans(DateTimeInterface $now): int;
    public function expireWaitingPlans(DateTimeInterface $now, int $limit): int;
    public function claimDuePlans(DateTimeInterface $now, int $limit, int $claimTimeoutSeconds): Collection;
    public function releaseExpiredClaims(DateTimeInterface $now, int $claimTimeoutSeconds): int;
    public function releaseClaim(NotificationPlan $plan, string $claimToken): bool;
    public function consumeClaim(string $planId, string $claimToken, DateTimeInterface $processingStartedAt): ?NotificationPlan;
    public function findDuplicate(NotificationPlanData $data): ?NotificationPlan;
    public function markRetryScheduled(NotificationPlan $plan, DateTimeInterface $nextAttemptAt, string $errorCode, string $errorMessage): NotificationPlan;
    public function findPrunablePlans(DateTimeInterface $before, int $limit): Collection;
    public function delete(NotificationPlan $plan): void;
    public function paginateForAdmin(NotificationPlanSearchData $search): LengthAwarePaginator;
}
