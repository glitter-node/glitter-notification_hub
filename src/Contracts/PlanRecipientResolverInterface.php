<?php
namespace Modules\Glitter\NotificationHub\Contracts;
use Modules\Glitter\NotificationHub\DTO\ResolvedPlanRecipientData;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
interface PlanRecipientResolverInterface { public function resolve(NotificationPlan $plan): ResolvedPlanRecipientData; }
