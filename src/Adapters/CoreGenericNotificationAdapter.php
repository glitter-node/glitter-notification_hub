<?php
namespace Modules\Glitter\NotificationHub\Adapters;
use App\Notifications\GenericNotification;
use Modules\Glitter\NotificationHub\Contracts\CoreNotificationAdapterInterface;
use Modules\Glitter\NotificationHub\DTO\CoreNotificationDeliveryData;
use Modules\Glitter\NotificationHub\DTO\CoreNotificationDeliveryResultData;
use Modules\Glitter\NotificationHub\DTO\NotificationCorrelationData;
use Modules\Glitter\NotificationHub\Enums\CoreDeliveryOutcome;
use Modules\Glitter\NotificationHub\Exceptions\ExternalDeliveryDisabledException;
use Modules\Glitter\NotificationHub\Support\NotificationCorrelationRegistry;
class CoreGenericNotificationAdapter implements CoreNotificationAdapterInterface
{
    public function __construct(private readonly NotificationCorrelationRegistry $registry) {}
    public function isEnabledFor(string $channel):bool{return (bool)config('notification-hub.enabled',false)&&(bool)config('notification-hub.allow_external_delivery',false)&&in_array($channel,config('notification-hub.allowed_channels',[]),true)&&in_array($channel,['mail','database'],true);}
    public function deliver(CoreNotificationDeliveryData $data):CoreNotificationDeliveryResultData
    {
        if(!$this->isEnabledFor($data->channel))throw new ExternalDeliveryDisabledException;
        $notification=new GenericNotification($data->definitionType,$data->hookPrefix,$data->data,$data->extensionType,$data->extensionIdentifier,$data->channel);
        $key=$this->registry->register(GenericNotification::class,$data->definitionType,$data->extensionType,$data->extensionIdentifier,$data->notifiable,$data->channel,new NotificationCorrelationData($data->planId,$data->attemptId,$data->correlationId));
        try{$data->notifiable->notify($notification);return $this->registry->result($key)??new CoreNotificationDeliveryResultData(CoreDeliveryOutcome::Failed,'hook_result_missing',null,null,null,null,null,null);}
        finally{$this->registry->forget($key);}
    }
}
