<?php

namespace Modules\Glitter\NotificationHub\Services\Admin;

use App\Extension\HookManager;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Glitter\NotificationHub\Contracts\NotificationHubInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\AttemptRepositoryInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\DeadLetterRepositoryInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\NotificationHubTransactionInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\PlanRepositoryInterface;
use Modules\Glitter\NotificationHub\DTO\Admin\AdminManualRetryResultData;
use Modules\Glitter\NotificationHub\DTO\Admin\ManualRetryData;
use Modules\Glitter\NotificationHub\DTO\Admin\NotificationAttemptSearchData;
use Modules\Glitter\NotificationHub\DTO\Admin\NotificationDeadLetterSearchData;
use Modules\Glitter\NotificationHub\DTO\Admin\NotificationPlanSearchData;
use Modules\Glitter\NotificationHub\DTO\NotificationPlanData;
use Modules\Glitter\NotificationHub\DTO\NotificationResultData;
use Modules\Glitter\NotificationHub\Enums\PlanStatus;
use Modules\Glitter\NotificationHub\Exceptions\AdminActionConflictException;
use Modules\Glitter\NotificationHub\Exceptions\AdminResourceNotFoundException;
use Modules\Glitter\NotificationHub\Exceptions\DuplicatePlanConsistencyException;
use Modules\Glitter\NotificationHub\Models\NotificationDeadLetter;
use Modules\Glitter\NotificationHub\Models\NotificationDeliveryAttempt;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use Modules\Glitter\NotificationHub\Services\PlanStateTransitionService;
use Modules\Glitter\NotificationHub\Support\DeliveryMetadataSanitizer;

class NotificationHubAdminService
{
    public function __construct(
        private readonly PlanRepositoryInterface $plans,
        private readonly AttemptRepositoryInterface $attempts,
        private readonly DeadLetterRepositoryInterface $deadLetters,
        private readonly NotificationHubInterface $hub,
        private readonly PlanStateTransitionService $transitions,
        private readonly DeliveryMetadataSanitizer $sanitizer,
        private readonly NotificationHubTransactionInterface $transaction,
    ) {}

    public function listPlans(NotificationPlanSearchData $search): LengthAwarePaginator { return $this->plans->paginateForAdmin($search); }
    public function getPlan(string $id): NotificationPlan { return $this->plans->findById($id) ?? throw new AdminResourceNotFoundException; }
    public function listAttempts(NotificationAttemptSearchData $search): LengthAwarePaginator { return $this->attempts->paginateForAdmin($search); }
    public function getAttempt(string $id): NotificationDeliveryAttempt { return $this->attempts->findById($id) ?? throw new AdminResourceNotFoundException; }
    public function listDeadLetters(NotificationDeadLetterSearchData $search): LengthAwarePaginator { return $this->deadLetters->paginateForAdmin($search); }
    public function getDeadLetter(string $id): NotificationDeadLetter { return $this->deadLetters->findById($id) ?? throw new AdminResourceNotFoundException; }

    public function cancelPlan(string $id, ?string $reason, ?int $actor): NotificationResultData
    {
        $plan = $this->getPlan($id);
        if (! in_array($plan->status, [PlanStatus::Pending, PlanStatus::Scheduled, PlanStatus::RetryScheduled, PlanStatus::Claimed], true)) {
            throw new AdminActionConflictException('plan_not_cancellable');
        }
        $plan = $this->transitions->markCancelled($plan, CarbonImmutable::now(), $this->sanitizer->sanitizeText($reason, 500), $actor);
        HookManager::doAction('glitter-notification_hub.plan.after_cancel', ['plan_id' => $plan->id, 'actor_id' => $actor, 'reason_code' => $reason ? 'admin_reason' : null]);

        return $this->result($plan);
    }

    public function retryPlan(string $id, ManualRetryData $data): AdminManualRetryResultData
    {
        $resolved = $this->withRequestId($data);

        return $this->transaction->run(fn (): AdminManualRetryResultData => new AdminManualRetryResultData($this->retryPlanNow($id, $resolved), $resolved->requestId));
    }

    public function resolveDeadLetter(string $id, string $note, ?int $actor): NotificationDeadLetter
    {
        $deadLetter = $this->getDeadLetter($id);
        if ($deadLetter->resolved_at !== null) throw new AdminActionConflictException('dead_letter_already_resolved');
        $deadLetter = $this->deadLetters->resolve($deadLetter, CarbonImmutable::now(), $actor, (string) $this->sanitizer->sanitizeText($note, 1000));
        HookManager::doAction('glitter-notification_hub.dead_letter.after_resolve', ['dead_letter_id' => $deadLetter->id, 'plan_id' => $deadLetter->plan_id, 'actor_id' => $actor, 'reason_code' => 'resolved']);

        return $deadLetter;
    }

    public function retryDeadLetter(string $id, ManualRetryData $data): AdminManualRetryResultData
    {
        $resolved = $this->withRequestId($data);

        return $this->transaction->run(function () use ($id, $resolved): AdminManualRetryResultData {
            $deadLetter = $this->deadLetters->findByIdForUpdate($id) ?? throw new AdminResourceNotFoundException;
            $original = $this->getPlan((string) $deadLetter->plan_id);
            $input = $this->manualRetryInput($original, $resolved);
            $existing = $this->plans->findDuplicate($input);

            if ($existing !== null) {
                if ((string) $existing->retry_of_plan_id !== (string) $original->id) {
                    throw new DuplicatePlanConsistencyException;
                }

                return new AdminManualRetryResultData($this->result($existing, true), $resolved->requestId);
            }

            if ($deadLetter->resolved_at !== null) throw new AdminActionConflictException('dead_letter_already_resolved');

            $result = $this->retryPlanNow((string) $original->id, $resolved, $input);
            $this->deadLetters->resolve($deadLetter, CarbonImmutable::now(), $resolved->actorId, (string) $this->sanitizer->sanitizeText($resolved->reason, 1000));
            HookManager::doAction('glitter-notification_hub.dead_letter.after_manual_retry', ['dead_letter_id' => $id, 'plan_id' => $original->id, 'new_plan_id' => $result->planId, 'actor_id' => $resolved->actorId, 'reason_code' => 'manual_retry']);

            return new AdminManualRetryResultData($result, $resolved->requestId);
        });
    }

    private function retryPlanNow(string $id, ManualRetryData $data, ?NotificationPlanData $input = null): NotificationResultData
    {
        $original = $this->getPlan($id);
        if (! in_array($original->status, [PlanStatus::Failed, PlanStatus::Skipped, PlanStatus::Expired, PlanStatus::Cancelled], true)) {
            throw new AdminActionConflictException('plan_not_retryable');
        }
        $result = $this->hub->createPlan($input ?? $this->manualRetryInput($original, $data));
        $newPlan = $this->getPlan($result->planId);
        if (! $result->isDuplicate) {
            $newPlan = $this->plans->updateState($newPlan, $newPlan->status, ['retry_of_plan_id' => $original->id, 'manual_retry_reason' => $this->sanitizer->sanitizeText($data->reason, 500), 'manual_retry_by' => $data->actorId]);
            HookManager::doAction('glitter-notification_hub.plan.after_manual_retry', ['plan_id' => $original->id, 'new_plan_id' => $newPlan->id, 'actor_id' => $data->actorId, 'reason_code' => 'manual_retry']);
        }

        return $this->result($newPlan, $result->isDuplicate);
    }

    private function manualRetryInput(NotificationPlan $plan, ManualRetryData $data): NotificationPlanData
    {
        return new NotificationPlanData($plan->definition_type, $plan->hook_prefix ?? '', $plan->extension_type, $plan->extension_identifier, $plan->source_type, $plan->source_id, $plan->recipient_type, $plan->recipient_id, $plan->recipient_identifier_hash, $plan->recipient_snapshot ?? [], $plan->channel, $plan->data ?? [], $plan->context ?? [], $plan->policy_snapshot ?? [], 'manual-retry:'.$plan->id.':'.$data->requestId, $data->retryPolicy, $data->maxAttempts, $data->scheduledAt, null, $data->actorId);
    }

    private function withRequestId(ManualRetryData $data): ManualRetryData
    {
        return new ManualRetryData($data->requestId ?? Str::uuid()->toString(), $data->reason, $data->scheduledAt, $data->retryPolicy, $data->maxAttempts, $data->actorId);
    }

    private function result(NotificationPlan $plan, bool $duplicate = false): NotificationResultData
    {
        return new NotificationResultData((string) $plan->id, $plan->correlation_id, $plan->status, $duplicate);
    }
}
