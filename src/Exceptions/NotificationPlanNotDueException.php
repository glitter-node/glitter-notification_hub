<?php

namespace Modules\Glitter\NotificationHub\Exceptions;

use DomainException;

class NotificationPlanNotDueException extends DomainException
{
    public function __construct()
    {
        parent::__construct(__('glitter-notification_hub::exceptions.plan_not_due'));
    }
}
