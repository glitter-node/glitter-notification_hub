<?php

namespace Modules\Glitter\NotificationHub\Enums;

enum PlanStatus: string
{
    case Pending = 'pending';
    case Scheduled = 'scheduled';
    case Claimed = 'claimed';
    case Processing = 'processing';
    case Accepted = 'accepted';
    case RetryScheduled = 'retry_scheduled';
    case Skipped = 'skipped';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Pending, self::Scheduled, self::RetryScheduled => in_array($next, [self::Claimed, self::Cancelled, self::Expired], true),
            self::Claimed => in_array($next, [self::Processing, self::Pending, self::Scheduled, self::RetryScheduled, self::Skipped, self::Cancelled, self::Expired], true),
            self::Processing => in_array($next, [self::Accepted, self::RetryScheduled, self::Failed, self::Skipped], true),
            default => false,
        };
    }
}
