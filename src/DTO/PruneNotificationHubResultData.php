<?php

namespace Modules\Glitter\NotificationHub\DTO;

final readonly class PruneNotificationHubResultData
{
    public function __construct(public int $candidatePlans, public int $deletedPlans, public int $deletedAttempts, public int $deletedDeadLetters, public int $protectedUnresolvedDeadLetters) {}
    public function toArray(): array { return get_object_vars($this); }
}
