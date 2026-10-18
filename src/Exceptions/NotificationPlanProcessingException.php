<?php
namespace Modules\Glitter\NotificationHub\Exceptions;
use DomainException;
class NotificationPlanProcessingException extends DomainException
{
    public function __construct() { parent::__construct(__('glitter-notification_hub::exceptions.plan_processing')); }
}
