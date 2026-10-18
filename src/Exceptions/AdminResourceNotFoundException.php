<?php
namespace Modules\Glitter\NotificationHub\Exceptions;use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;class AdminResourceNotFoundException extends NotFoundHttpException{public function __construct(){parent::__construct(__('glitter-notification_hub::exceptions.admin_resource_not_found'));}}
