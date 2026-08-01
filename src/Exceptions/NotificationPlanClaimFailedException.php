<?php
namespace Modules\Glitter\NotificationHub\Exceptions;
use DomainException;
class NotificationPlanClaimFailedException extends DomainException { public function __construct(){parent::__construct(__('glitter-notification_hub::exceptions.plan_claim_required'));} }
