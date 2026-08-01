<?php
namespace Modules\Glitter\NotificationHub\Exceptions;
use DomainException;
class NotificationPlanAlreadyTerminalException extends DomainException
{
    public function __construct() { parent::__construct(__('glitter-notification_hub::exceptions.plan_already_terminal')); }
}
