<?php

namespace Modules\Glitter\NotificationHub\Repositories;

use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Glitter\NotificationHub\DTO\Admin\NotificationDeadLetterSearchData;
use Modules\Glitter\NotificationHub\Contracts\Repositories\DeadLetterRepositoryInterface;
use Modules\Glitter\NotificationHub\Models\NotificationDeadLetter;
use Modules\Glitter\NotificationHub\Models\NotificationDeliveryAttempt;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;

class DeadLetterRepository implements DeadLetterRepositoryInterface
{
    public function __construct(private readonly NotificationDeadLetter $model) {}

    public function createForFailure(NotificationPlan $plan, ?NotificationDeliveryAttempt $attempt, string $reasonCode, ?string $reasonMessage, DateTimeInterface $failedAt, bool $retryExhausted): NotificationDeadLetter
    {
        try {
            return $this->model->newQuery()->create(['id' => (string) Str::uuid(), 'plan_id' => $plan->getKey(), 'attempt_id' => $attempt?->getKey(), 'reason_code' => $reasonCode, 'reason_message' => $reasonMessage, 'failed_at' => $failedAt, 'retry_exhausted' => $retryExhausted]);
        } catch (QueryException $exception) {
            if ((string) ($exception->errorInfo[0] ?? '') !== '23000' || (int) ($exception->errorInfo[1] ?? 0) !== 1062) throw $exception;
            return $this->findByPlanId((string) $plan->getKey()) ?? throw $exception;
        }
    }

    public function findByPlanId(string $planId): ?NotificationDeadLetter { return $this->model->newQuery()->where('plan_id', $planId)->first(); }
    public function findResolvedBefore(DateTimeInterface $before, int $limit): Collection { return $this->model->newQuery()->whereNotNull('resolved_at')->where('resolved_at', '<', $before)->limit($limit)->get(); }
    public function delete(NotificationDeadLetter $deadLetter): void { $deadLetter->delete(); }
    public function findById(string $id): ?NotificationDeadLetter { return $this->model->newQuery()->find($id); }
    public function findByIdForUpdate(string $id): ?NotificationDeadLetter { return $this->model->newQuery()->whereKey($id)->lockForUpdate()->first(); }
    public function paginateForAdmin(NotificationDeadLetterSearchData $search): LengthAwarePaginator { $q=$this->model->newQuery();foreach(['retry_exhausted','reason_code','plan_id','attempt_id'] as $k)if(array_key_exists($k,$search->filters))$q->where($k,$search->filters[$k]);if(array_key_exists('resolved',$search->filters))$search->filters['resolved']?$q->whereNotNull('resolved_at'):$q->whereNull('resolved_at');if(isset($search->filters['failed_from']))$q->where('failed_at','>=',$search->filters['failed_from']);if(isset($search->filters['failed_to']))$q->where('failed_at','<=',$search->filters['failed_to']);if($t=$search->filters['search']??null)$q->where(fn($x)=>$x->where('reason_code','like',"%$t%")->orWhere('plan_id','like',"%$t%")->orWhere('attempt_id','like',"%$t%"));if($search->sort==='unresolved_first')return $q->orderBy('resolved_at')->orderByDesc('failed_at')->paginate($search->perPage);$dir=str_starts_with($search->sort,'-')?'desc':'asc';$col=ltrim($search->sort,'-');if(!in_array($col,['failed_at','resolved_at','created_at'],true))$col='failed_at';return $q->orderBy($col,$dir)->paginate($search->perPage);}
    public function resolve(NotificationDeadLetter $deadLetter, DateTimeInterface $resolvedAt, ?int $resolvedBy, string $note): NotificationDeadLetter { $deadLetter->forceFill(['resolved_at'=>$resolvedAt,'resolved_by'=>$resolvedBy,'resolution_note'=>$note])->save();return $deadLetter->refresh(); }
}
