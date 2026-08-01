<?php

namespace Modules\Glitter\NotificationHub\DTO;

use Modules\Glitter\NotificationHub\Enums\PlanStatus;

final readonly class NotificationResultData
{
    public function __construct(
        public string $planId,
        public string $correlationId,
        public PlanStatus $status,
        public bool $isDuplicate,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'plan_id' => $this->planId,
            'correlation_id' => $this->correlationId,
            'status' => $this->status->value,
            'is_duplicate' => $this->isDuplicate,
        ];
    }
}
