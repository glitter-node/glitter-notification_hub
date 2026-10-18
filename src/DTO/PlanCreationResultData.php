<?php

namespace Modules\Glitter\NotificationHub\DTO;

use Modules\Glitter\NotificationHub\Models\NotificationPlan;

/** Internal result only. The model must be serialized through an API Resource. */
final readonly class PlanCreationResultData
{
    public function __construct(
        public NotificationPlan $plan,
        public bool $isDuplicate,
    ) {}

    /** @return array{plan_id: string, is_duplicate: bool} */
    public function toArray(): array
    {
        return ['plan_id' => (string) $this->plan->getKey(), 'is_duplicate' => $this->isDuplicate];
    }
}
