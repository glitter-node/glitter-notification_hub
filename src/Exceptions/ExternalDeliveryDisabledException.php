<?php
namespace Modules\Glitter\NotificationHub\Exceptions;
use RuntimeException;
class ExternalDeliveryDisabledException extends RuntimeException
{
    public function __construct() { parent::__construct(__('glitter-notification_hub::exceptions.external_delivery_disabled')); }
}
