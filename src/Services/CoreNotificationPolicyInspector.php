<?php
namespace Modules\Glitter\NotificationHub\Services;
use App\Contracts\Notifications\ChannelReadinessCheckerInterface;
use App\Services\NotificationChannelService;
use App\Services\NotificationDefinitionService;
use App\Services\NotificationTemplateService;
use Modules\Glitter\NotificationHub\Contracts\CoreNotificationPolicyInspectorInterface;
use Modules\Glitter\NotificationHub\DTO\CoreNotificationPolicyResultData;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
class CoreNotificationPolicyInspector implements CoreNotificationPolicyInspectorInterface
{
    public function __construct(private readonly NotificationDefinitionService $definitions,private readonly NotificationTemplateService $templates,private readonly NotificationChannelService $channels,private readonly ChannelReadinessCheckerInterface $readiness) {}
    public function inspect(NotificationPlan $plan,bool $isGuest):CoreNotificationPolicyResultData
    {
        $definition=$this->definitions->resolve($plan->definition_type);if($definition===null)return $this->deny('definition_unavailable');
        if(!in_array($plan->channel,$definition->channels??[],true))return $this->deny('channel_not_defined');
        if($this->templates->resolve($plan->definition_type,$plan->channel)===null)return $this->deny('template_unavailable');
        if(!$this->channels->isChannelEnabledForExtension($plan->extension_type,$plan->extension_identifier,$plan->channel))return $this->deny('channel_disabled');
        if(!$this->readiness->isReady($plan->channel))return $this->deny('channel_not_ready');
        if($isGuest&&!$this->channels->isChannelGuestAllowed($plan->channel))return $this->deny('guest_not_allowed');
        return new CoreNotificationPolicyResultData(true);
    }
    private function deny(string $code):CoreNotificationPolicyResultData{return new CoreNotificationPolicyResultData(false,$code,__('glitter-notification_hub::exceptions.policy_'.$code));}
}
