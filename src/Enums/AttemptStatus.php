<?php

namespace Modules\Glitter\NotificationHub\Enums;

enum AttemptStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Accepted = 'accepted';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Pending => in_array($next, [self::Processing, self::Cancelled], true),
            self::Processing => in_array($next, [self::Accepted, self::Failed, self::Cancelled], true),
            default => false,
        };
    }
}
