<?php

namespace Modules\Glitter\NotificationHub\Services;

use Carbon\CarbonImmutable;
use Modules\Glitter\NotificationHub\Contracts\CoreNotificationAdapterInterface;
use Modules\Glitter\NotificationHub\Contracts\CoreNotificationPolicyInspectorInterface;
use Modules\Glitter\NotificationHub\Contracts\PlanRecipientResolverInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\PlanRepositoryInterface;
use Modules\Glitter\NotificationHub\Contracts\RetryDecisionServiceInterface;
use Modules\Glitter\NotificationHub\DTO\CoreNotificationDeliveryData;
use Modules\Glitter\NotificationHub\DTO\NotificationResultData;
use Modules\Glitter\NotificationHub\Enums\CoreDeliveryOutcome;
use Modules\Glitter\NotificationHub\Enums\PlanStatus;
use Modules\Glitter\NotificationHub\Exceptions\ExternalDeliveryDisabledException;
use Modules\Glitter\NotificationHub\Exceptions\PlanRecipientResolutionException;
use Modules\Glitter\NotificationHub\Exceptions\NotificationPlanClaimFailedException;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;

class NotificationDeliveryOrchestrator
{
    public function __construct(
        private readonly PlanRepositoryInterface $plans,
        private readonly PlanRecipientResolverInterface $recipients,
        private readonly CoreNotificationPolicyInspectorInterface $policy,
        private readonly CoreNotificationAdapterInterface $adapter,
        private readonly PlanStateTransitionService $planTransitions,
        private readonly AttemptLifecycleService $attempts,
        private readonly RetryDecisionServiceInterface $retryDecisions,
        private readonly DeadLetterService $deadLetters,
    ) {}

    public function deliverProcessingPlan(NotificationPlan $plan): NotificationResultData
    {
        if ($plan->status !== PlanStatus::Processing) {
            throw new NotificationPlanClaimFailedException;
        }

        $now = CarbonImmutable::now();
        if ($plan->expires_at !== null && $plan->expires_at->lte($now)) {
            $plan = $this->planTransitions->markExpired($plan, $plan->expires_at);
            return $this->result($plan);
        }
        if (! $this->adapter->isEnabledFor($plan->channel)) {
            throw new ExternalDeliveryDisabledException;
        }

        $policy = $this->policy->inspect($plan, $plan->recipient_type === 'guest');
        if (! $policy->allowed) {
            return $this->result($this->planTransitions->markSkipped($plan, $policy->reasonCode, $policy->reasonMessage));
        }

        try {
            $recipient = $this->recipients->resolve($plan);
        } catch (PlanRecipientResolutionException $exception) {
            return $this->result($this->planTransitions->markSkipped($plan, 'recipient_unavailable', $exception->getMessage()));
        }

        $attempt = $this->attempts->start($plan, $now);
        $delivery = new CoreNotificationDeliveryData(
            (string) $plan->getKey(),
            (string) $attempt->getKey(),
            $plan->correlation_id,
            $plan->definition_type,
            $plan->hook_prefix ?? '',
            $plan->extension_type,
            $plan->extension_identifier,
            $plan->channel,
            $plan->data ?? [],
            $recipient->notifiable,
        );

        $outcome = $this->adapter->deliver($delivery);

        $completedAt = CarbonImmutable::now();
        if ($outcome->outcome === CoreDeliveryOutcome::Accepted) {
            $this->attempts->markAccepted($attempt, $completedAt, $outcome->providerMessageId, $outcome->responseStatus, $outcome->responseMetadata);
            return $this->result($this->planTransitions->markAccepted($plan, $completedAt));
        }

        if ($outcome->outcome === CoreDeliveryOutcome::Skipped) {
            $this->attempts->cancel($attempt, $completedAt);
            return $this->result($this->planTransitions->markSkipped($plan, $outcome->reasonCode, $outcome->reasonMessage));
        }

        $attempt = $this->attempts->markFailed($attempt, $completedAt, false, null, 'core', $outcome->reasonCode, $outcome->reasonMessage, $outcome->responseStatus, $outcome->responseMetadata);
        $decision = $this->retryDecisions->decide($plan, $attempt, $outcome, $completedAt);
        if ($decision->shouldRetry && $decision->nextAttemptAt !== null) {
            return $this->result($this->planTransitions->markRetryScheduled($plan, $decision->nextAttemptAt, $outcome->reasonCode, $outcome->reasonMessage));
        }

        $plan = $this->planTransitions->markFailed($plan, $completedAt, $decision->terminalReasonCode ?? $outcome->reasonCode, $decision->terminalReasonMessage ?? $outcome->reasonMessage);
        $this->deadLetters->record($plan, $attempt, $decision->terminalReasonCode ?? 'delivery_failed', $decision->terminalReasonMessage, $completedAt, in_array($decision->terminalReasonCode, ['retry_exhausted', 'retry_backoff_exhausted'], true));
        return $this->result($plan);
    }

    private function result(object $plan): NotificationResultData
    {
        return new NotificationResultData((string) $plan->getKey(), $plan->correlation_id, $plan->status, false);
    }
}
