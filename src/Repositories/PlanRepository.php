<?php

namespace Modules\Glitter\NotificationHub\Repositories;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Glitter\NotificationHub\DTO\Admin\NotificationPlanSearchData;
use Modules\Glitter\NotificationHub\Contracts\Repositories\PlanRepositoryInterface;
use Modules\Glitter\NotificationHub\DTO\NotificationPlanData;
use Modules\Glitter\NotificationHub\DTO\PlanCreationResultData;
use Modules\Glitter\NotificationHub\Enums\PlanStatus;
use Modules\Glitter\NotificationHub\Exceptions\DuplicatePlanConsistencyException;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use Modules\Glitter\NotificationHub\Support\IdempotencyHasher;
use Modules\Glitter\NotificationHub\Support\RecipientIdentityHasher;

class PlanRepository implements PlanRepositoryInterface
{
    public function __construct(
        private readonly NotificationPlan $model,
        private readonly IdempotencyHasher $idempotencyHasher,
        private readonly RecipientIdentityHasher $recipientIdentityHasher,
    ) {}

    public function create(NotificationPlanData $data): NotificationPlan
    {
        return $this->model->newQuery()->create([
            ...$data->toArray(),
            'id' => (string) Str::uuid(),
            'correlation_id' => (string) Str::uuid(),
            'source_id' => $data->sourceId === null ? null : (string) $data->sourceId,
            'recipient_identity_hash' => $this->makeRecipientIdentityHash($data),
            'idempotency_hash' => $this->makeIdempotencyHash($data),
            'status' => PlanStatus::Pending,
            'attempt_count' => 0,
        ]);
    }

    public function createOrFindDuplicate(NotificationPlanData $data): PlanCreationResultData
    {
        try {
            return new PlanCreationResultData($this->create($data), false);
        } catch (QueryException $exception) {
            if (! $this->isMariaDbDuplicateKey($exception)) {
                throw $exception;
            }

            $existing = $this->findByIdempotencyIdentity(
                $data->extensionType,
                $data->extensionIdentifier,
                $data->definitionType,
                $this->makeIdempotencyHash($data),
                $this->makeRecipientIdentityHash($data),
                $data->channel,
            );

            if ($existing === null) {
                throw new DuplicatePlanConsistencyException($exception);
            }

            return new PlanCreationResultData($existing, true);
        }
    }

    public function findById(string $id): ?NotificationPlan
    {
        return $this->model->newQuery()->find($id);
    }

    public function findByCorrelationId(string $correlationId): ?NotificationPlan
    {
        return $this->model->newQuery()
            ->where('correlation_id', $correlationId)
            ->first();
    }

    public function findByIdempotencyIdentity(
        string $extensionType,
        string $extensionIdentifier,
        string $definitionType,
        string $idempotencyHash,
        string $recipientIdentityHash,
        string $channel,
    ): ?NotificationPlan {
        return $this->model->newQuery()
            ->where('extension_type', $extensionType)
            ->where('extension_identifier', $extensionIdentifier)
            ->where('definition_type', $definitionType)
            ->where('idempotency_hash', $idempotencyHash)
            ->where('recipient_identity_hash', $recipientIdentityHash)
            ->where('channel', $channel)
            ->first();
    }

    public function makeRecipientIdentityHash(NotificationPlanData $data): string
    {
        return $this->recipientIdentityHasher->hash($data);
    }

    public function updateState(NotificationPlan $plan, PlanStatus $status, array $attributes = []): NotificationPlan
    {
        $plan->forceFill([...$attributes, 'status' => $status])->save();

        return $plan->refresh();
    }

    public function incrementAttemptCount(NotificationPlan $plan): NotificationPlan
    {
        $plan->increment('attempt_count');

        return $plan->refresh();
    }

    public function countDuePlans(DateTimeInterface $now): int { return $this->dueQuery($now)->count(); }
    public function countExpiredWaitingPlans(DateTimeInterface $now): int { return $this->expiredWaitingQuery($now)->count(); }
    public function expireWaitingPlans(DateTimeInterface $now, int $limit): int
    {
        return DB::transaction(function () use ($now, $limit): int {
            $plans = $this->expiredWaitingQuery($now)->limit(max(1, $limit))->lock('for update skip locked')->get();
            foreach ($plans as $plan) $plan->forceFill(['status' => PlanStatus::Expired, 'failed_at' => null])->save();
            return $plans->count();
        });
    }

    public function claimDuePlans(DateTimeInterface $now, int $limit, int $claimTimeoutSeconds): Collection
    {
        return DB::transaction(function () use ($now, $limit): Collection {
            $plans = $this->dueQuery($now)->orderBy('created_at')->limit(max(1, $limit))->lock('for update skip locked')->get();
            foreach ($plans as $plan) {
                $plan->forceFill(['claimed_from_status' => $plan->status, 'status' => PlanStatus::Claimed, 'claim_token' => (string) Str::uuid(), 'claimed_at' => $now, 'processing_started_at' => null])->save();
                $plan->refresh();
            }
            return $plans;
        });
    }

    public function releaseExpiredClaims(DateTimeInterface $now, int $claimTimeoutSeconds): int
    {
        $cutoff = \DateTimeImmutable::createFromInterface($now)->modify('-'.max(1, $claimTimeoutSeconds).' seconds');
        return DB::transaction(function () use ($cutoff): int {
            $plans = $this->model->newQuery()->where('status', PlanStatus::Claimed->value)->where('claimed_at', '<', $cutoff)->lock('for update skip locked')->get();
            foreach ($plans as $plan) $this->restoreClaim($plan);
            return $plans->count();
        });
    }

    public function releaseClaim(NotificationPlan $plan, string $claimToken): bool
    {
        if ($plan->status !== PlanStatus::Claimed || ! hash_equals((string) $plan->claim_token, $claimToken)) return false;
        $status = $plan->claimed_from_status instanceof PlanStatus ? $plan->claimed_from_status : PlanStatus::from((string) $plan->claimed_from_status);
        return $this->model->newQuery()->whereKey($plan->getKey())->where('status', PlanStatus::Claimed->value)->where('claim_token', $claimToken)->update(['status' => $status->value, 'claimed_from_status' => null, 'claim_token' => null, 'claimed_at' => null, 'updated_at' => now()]) === 1;
    }

    public function consumeClaim(string $planId, string $claimToken, DateTimeInterface $processingStartedAt): ?NotificationPlan
    {
        return DB::transaction(function () use ($planId, $claimToken, $processingStartedAt): ?NotificationPlan {
            /** @var NotificationPlan|null $plan */
            $plan = $this->model->newQuery()->whereKey($planId)->lockForUpdate()->first();
            if ($plan === null || $plan->status !== PlanStatus::Claimed || ! is_string($plan->claim_token) || ! hash_equals($plan->claim_token, $claimToken)) {
                return null;
            }

            $plan->forceFill([
                'status' => PlanStatus::Processing,
                'processing_started_at' => $processingStartedAt,
                'claim_token' => null,
                'claimed_at' => null,
                'claimed_from_status' => null,
            ])->save();

            return $plan->refresh();
        });
    }

    public function findDuplicate(NotificationPlanData $data): ?NotificationPlan
    {
        return $this->findByIdempotencyIdentity(
            $data->extensionType,
            $data->extensionIdentifier,
            $data->definitionType,
            $this->makeIdempotencyHash($data),
            $this->makeRecipientIdentityHash($data),
            $data->channel,
        );
    }

    public function markRetryScheduled(NotificationPlan $plan, DateTimeInterface $nextAttemptAt, string $errorCode, string $errorMessage): NotificationPlan
    {
        return $this->updateState($plan, PlanStatus::RetryScheduled, ['next_attempt_at' => $nextAttemptAt, 'last_error_code' => $errorCode, 'last_error_message' => $errorMessage, 'claim_token' => null, 'claimed_at' => null, 'claimed_from_status' => null]);
    }

    public function findPrunablePlans(DateTimeInterface $before, int $limit): Collection
    {
        return $this->model->newQuery()->whereIn('status', [PlanStatus::Accepted->value, PlanStatus::Skipped->value, PlanStatus::Failed->value, PlanStatus::Cancelled->value, PlanStatus::Expired->value])->where('updated_at', '<', $before)->limit(max(1, $limit))->get();
    }

    public function delete(NotificationPlan $plan): void { $plan->delete(); }

    public function paginateForAdmin(NotificationPlanSearchData $search): LengthAwarePaginator
    {
        $query=$this->model->newQuery()->withCount('attempts')->withExists('deadLetter');
        foreach(['status','definition_type','channel','extension_type','extension_identifier','recipient_type','source_type','source_id','correlation_id'] as $key)if(isset($search->filters[$key]))$query->where($key,$search->filters[$key]);
        if(isset($search->filters['created_from']))$query->where('created_at','>=',$search->filters['created_from']);if(isset($search->filters['created_to']))$query->where('created_at','<=',$search->filters['created_to']);if(isset($search->filters['scheduled_from']))$query->where('scheduled_at','>=',$search->filters['scheduled_from']);if(isset($search->filters['scheduled_to']))$query->where('scheduled_at','<=',$search->filters['scheduled_to']);
        if(array_key_exists('has_dead_letter',$search->filters))$search->filters['has_dead_letter']?$query->whereHas('deadLetter'):$query->whereDoesntHave('deadLetter');
        if($term=$search->filters['search']??null)$query->where(fn(Builder $q)=>$q->where('correlation_id','like',"%$term%")->orWhere('source_id','like',"%$term%")->orWhere('definition_type','like',"%$term%")->orWhere('extension_identifier','like',"%$term%")->orWhere('last_error_code','like',"%$term%"));
        [$column,$direction]=$this->sort($search->sort,['created_at','scheduled_at','next_attempt_at','accepted_at','failed_at','attempt_count'],'created_at');return $query->orderBy($column,$direction)->paginate($search->perPage);
    }

    private function sort(string $sort,array $allowed,string $default):array{$direction=str_starts_with($sort,'-')?'desc':'asc';$column=ltrim($sort,'-');return [in_array($column,$allowed,true)?$column:$default,$direction];}

    private function dueQuery(DateTimeInterface $now): Builder
    {
        return $this->model->newQuery()->where(function (Builder $query) use ($now): void {
            $query->where('status', PlanStatus::Pending->value)
                ->orWhere(fn (Builder $q) => $q->where('status', PlanStatus::Scheduled->value)->where('scheduled_at', '<=', $now))
                ->orWhere(fn (Builder $q) => $q->where('status', PlanStatus::RetryScheduled->value)->where('next_attempt_at', '<=', $now));
        })->where(fn (Builder $query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', $now))->whereColumn('attempt_count', '<', 'max_attempts');
    }

    private function expiredWaitingQuery(DateTimeInterface $now): Builder
    {
        return $this->model->newQuery()->whereIn('status', [PlanStatus::Pending->value, PlanStatus::Scheduled->value, PlanStatus::RetryScheduled->value])->whereNotNull('expires_at')->where('expires_at', '<=', $now);
    }

    private function restoreClaim(NotificationPlan $plan): void
    {
        $status = $plan->claimed_from_status instanceof PlanStatus ? $plan->claimed_from_status : PlanStatus::from((string) $plan->claimed_from_status);
        $plan->forceFill(['status' => $status, 'claimed_from_status' => null, 'claim_token' => null, 'claimed_at' => null])->save();
    }

    private function makeIdempotencyHash(NotificationPlanData $data): string
    {
        return $this->idempotencyHasher->hash($data->extensionType, $data->extensionIdentifier, $data->definitionType, $data->idempotencyKey);
    }

    private function isMariaDbDuplicateKey(QueryException $exception): bool
    {
        return (string) ($exception->errorInfo[0] ?? '') === '23000'
            && (int) ($exception->errorInfo[1] ?? 0) === 1062;
    }
}
