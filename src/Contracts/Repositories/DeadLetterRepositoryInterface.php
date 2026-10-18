<?php

namespace Modules\Glitter\NotificationHub\Contracts\Repositories;

use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Glitter\NotificationHub\DTO\Admin\NotificationDeadLetterSearchData;
use Modules\Glitter\NotificationHub\Models\NotificationDeadLetter;
use Modules\Glitter\NotificationHub\Models\NotificationDeliveryAttempt;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;

interface DeadLetterRepositoryInterface
{
    public function createForFailure(NotificationPlan $plan, ?NotificationDeliveryAttempt $attempt, string $reasonCode, ?string $reasonMessage, DateTimeInterface $failedAt, bool $retryExhausted): NotificationDeadLetter;
    public function findByPlanId(string $planId): ?NotificationDeadLetter;
    public function findResolvedBefore(DateTimeInterface $before, int $limit): Collection;
    public function delete(NotificationDeadLetter $deadLetter): void;
    public function findById(string $id): ?NotificationDeadLetter;
    public function findByIdForUpdate(string $id): ?NotificationDeadLetter;
    public function paginateForAdmin(NotificationDeadLetterSearchData $search): LengthAwarePaginator;
    public function resolve(NotificationDeadLetter $deadLetter, DateTimeInterface $resolvedAt, ?int $resolvedBy, string $note): NotificationDeadLetter;
}
