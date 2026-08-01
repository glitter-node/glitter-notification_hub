<?php
namespace Modules\Glitter\NotificationHub\Exceptions;
use RuntimeException;
class NotificationPlanNotFoundException extends RuntimeException
{
    public function __construct() { parent::__construct(__('glitter-notification_hub::exceptions.plan_not_found')); }
}
