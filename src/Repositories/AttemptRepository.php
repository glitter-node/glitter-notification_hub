<?php

namespace Modules\Glitter\NotificationHub\Repositories;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;use Modules\Glitter\NotificationHub\DTO\Admin\NotificationAttemptSearchData;

use Illuminate\Support\Str;
use Modules\Glitter\NotificationHub\Contracts\Repositories\AttemptRepositoryInterface;
use Modules\Glitter\NotificationHub\Enums\AttemptStatus;
use Modules\Glitter\NotificationHub\Models\NotificationDeliveryAttempt;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;

class AttemptRepository implements AttemptRepositoryInterface
{
    public function __construct(private readonly NotificationDeliveryAttempt $model) {}

    public function createForPlan(
        NotificationPlan $plan,
        int $attemptNumber,
        AttemptStatus $status,
    ): NotificationDeliveryAttempt
    {
        return $this->model->newQuery()->create([
            'id' => (string) Str::uuid(),
            'plan_id' => $plan->getKey(),
            'attempt_number' => $attemptNumber,
            'status' => $status,
        ]);
    }

    public function findById(string $id): ?NotificationDeliveryAttempt
    {
        return $this->model->newQuery()->find($id);
    }

    public function findLatestForPlan(NotificationPlan $plan): ?NotificationDeliveryAttempt
    {
        return $this->model->newQuery()
            ->where('plan_id', $plan->getKey())
            ->orderByDesc('attempt_number')
            ->first();
    }

    public function updateState(NotificationDeliveryAttempt $attempt, AttemptStatus $status, array $attributes = []): NotificationDeliveryAttempt
    {
        $attempt->forceFill([...$attributes, 'status' => $status])->save();

        return $attempt->refresh();
    }

    public function createNextAttempt(NotificationPlan $plan, AttemptStatus $status, ?string $provider = null): NotificationDeliveryAttempt
    {
        return $this->model->getConnection()->transaction(function () use ($plan, $status, $provider): NotificationDeliveryAttempt {
            $lockedPlan = $plan->newQuery()->lockForUpdate()->findOrFail($plan->getKey());
            $attemptNumber = ((int) $lockedPlan->attempt_count) + 1;
            $attempt = $this->model->newQuery()->create([
                'id' => (string) Str::uuid(),
                'plan_id' => $lockedPlan->getKey(),
                'attempt_number' => $attemptNumber,
                'status' => $status,
                'provider' => $provider,
            ]);
            $lockedPlan->forceFill(['attempt_count' => $attemptNumber])->save();
            $plan->setAttribute('attempt_count', $attemptNumber);

            return $attempt;
        });
    }

    public function deleteForPlan(NotificationPlan $plan): int { return $this->model->newQuery()->where('plan_id', $plan->getKey())->delete(); }
    public function paginateForAdmin(NotificationAttemptSearchData $search): LengthAwarePaginator { $q=$this->model->newQuery();foreach(['plan_id','status','provider','retryable','error_category','error_code'] as $k)if(array_key_exists($k,$search->filters))$q->where($k,$search->filters[$k]);if(isset($search->filters['created_from']))$q->where('created_at','>=',$search->filters['created_from']);if(isset($search->filters['created_to']))$q->where('created_at','<=',$search->filters['created_to']);if($t=$search->filters['search']??null)$q->where(fn($x)=>$x->where('provider','like',"%$t%")->orWhere('error_category','like',"%$t%")->orWhere('error_code','like',"%$t%"));$dir=str_starts_with($search->sort,'-')?'desc':'asc';$col=ltrim($search->sort,'-');if(!in_array($col,['created_at','started_at','completed_at','attempt_number'],true))$col='created_at';return $q->orderBy($col,$dir)->paginate($search->perPage);}
}
