<?php
namespace Modules\Glitter\NotificationHub\Exceptions;
use DomainException;
use Modules\Glitter\NotificationHub\Enums\PlanStatus;
class InvalidPlanStateTransitionException extends DomainException
{
    public function __construct(PlanStatus $from, PlanStatus $to) { parent::__construct(__('glitter-notification_hub::exceptions.invalid_plan_transition', ['from' => $from->value, 'to' => $to->value])); }
}
