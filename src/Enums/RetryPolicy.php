<?php

namespace Modules\Glitter\NotificationHub\Enums;

enum RetryPolicy: string
{
    case None = 'none';
    case Standard = 'standard';
    case Aggressive = 'aggressive';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
