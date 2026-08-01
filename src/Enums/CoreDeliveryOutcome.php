<?php
namespace Modules\Glitter\NotificationHub\Enums;
enum CoreDeliveryOutcome: string
{
    case Accepted = 'accepted'; case Failed = 'failed'; case Skipped = 'skipped'; case Blocked = 'blocked';
    public static function values(): array { return array_column(self::cases(), 'value'); }
}
