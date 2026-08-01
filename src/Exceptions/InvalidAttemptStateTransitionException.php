<?php
namespace Modules\Glitter\NotificationHub\Exceptions;
use DomainException;
use Modules\Glitter\NotificationHub\Enums\AttemptStatus;
class InvalidAttemptStateTransitionException extends DomainException
{
    public function __construct(AttemptStatus $from, AttemptStatus $to) { parent::__construct(__('glitter-notification_hub::exceptions.invalid_attempt_transition', ['from' => $from->value, 'to' => $to->value])); }
}
