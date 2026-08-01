<?php
namespace Modules\Glitter\NotificationHub\Exceptions;
use RuntimeException;
class CorrelationScopeConflictException extends RuntimeException { public function __construct() { parent::__construct(__('glitter-notification_hub::exceptions.correlation_scope_conflict')); } }
