<?php
namespace Modules\Glitter\NotificationHub\Exceptions;
use RuntimeException;
class PlanRecipientResolutionException extends RuntimeException { public function __construct(string $reason) { parent::__construct(__('glitter-notification_hub::exceptions.recipient_resolution_failed', ['reason'=>$reason])); } }
