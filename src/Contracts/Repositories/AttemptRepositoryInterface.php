<?php

namespace Modules\Glitter\NotificationHub\Contracts\Repositories;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;use Modules\Glitter\NotificationHub\DTO\Admin\NotificationAttemptSearchData;

use Modules\Glitter\NotificationHub\Enums\AttemptStatus;
use Modules\Glitter\NotificationHub\Models\NotificationDeliveryAttempt;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;

interface AttemptRepositoryInterface
{
    public function createForPlan(
        NotificationPlan $plan,
        int $attemptNumber,
        AttemptStatus $status,
    ): NotificationDeliveryAttempt;

    public function findById(string $id): ?NotificationDeliveryAttempt;

    public function findLatestForPlan(NotificationPlan $plan): ?NotificationDeliveryAttempt;

    /** @param array<string, mixed> $attributes */
    public function updateState(NotificationDeliveryAttempt $attempt, AttemptStatus $status, array $attributes = []): NotificationDeliveryAttempt;

    public function createNextAttempt(NotificationPlan $plan, AttemptStatus $status, ?string $provider = null): NotificationDeliveryAttempt;
    public function deleteForPlan(NotificationPlan $plan): int;
    public function paginateForAdmin(NotificationAttemptSearchData $search): LengthAwarePaginator;
}
