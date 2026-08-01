<?php

namespace Modules\Glitter\NotificationHub\DTO;

use DateTimeImmutable;

final readonly class RetryDecisionData
{
    public function __construct(public bool $shouldRetry, public ?DateTimeImmutable $nextAttemptAt = null, public ?string $terminalReasonCode = null, public ?string $terminalReasonMessage = null) {}
    public function toArray(): array { return ['should_retry' => $this->shouldRetry, 'next_attempt_at' => $this->nextAttemptAt?->format(DATE_ATOM), 'terminal_reason_code' => $this->terminalReasonCode, 'terminal_reason_message' => $this->terminalReasonMessage]; }
}
