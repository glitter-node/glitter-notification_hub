<?php
namespace Modules\Glitter\NotificationHub\Exceptions;use Symfony\Component\HttpKernel\Exception\ConflictHttpException;class AdminActionConflictException extends ConflictHttpException{public function __construct(string $key){parent::__construct(__('glitter-notification_hub::exceptions.'.$key));}}
