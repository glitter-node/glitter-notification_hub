<?php
namespace Modules\Glitter\NotificationHub\Exceptions;
use RuntimeException;
use Throwable;
class DuplicatePlanConsistencyException extends RuntimeException
{
    public function __construct(?Throwable $previous = null) { parent::__construct(__('glitter-notification_hub::exceptions.duplicate_consistency'), 0, $previous); }
}
