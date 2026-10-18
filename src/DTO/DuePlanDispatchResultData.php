<?php

namespace Modules\Glitter\NotificationHub\DTO;

final readonly class DuePlanDispatchResultData
{
    public function __construct(public int $eligibleCount = 0, public int $claimedCount = 0, public int $dispatchedCount = 0, public int $expiredCount = 0, public int $releasedClaimCount = 0, public int $failedDispatchCount = 0, public bool $disabled = false) {}
    public function toArray(): array { return get_object_vars($this); }
}
