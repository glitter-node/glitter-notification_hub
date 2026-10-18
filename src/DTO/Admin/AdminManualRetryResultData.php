<?php

namespace Modules\Glitter\NotificationHub\DTO\Admin;

use Modules\Glitter\NotificationHub\DTO\NotificationResultData;

final readonly class AdminManualRetryResultData
{
    public function __construct(public NotificationResultData $result, public string $requestId) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [...$this->result->toArray(), 'request_id' => $this->requestId];
    }
}
