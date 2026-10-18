<?php
namespace Modules\Glitter\NotificationHub\Support;
use Modules\Glitter\NotificationHub\DTO\CoreNotificationDeliveryResultData;
use Modules\Glitter\NotificationHub\DTO\NotificationCorrelationData;
use Modules\Glitter\NotificationHub\Enums\CoreDeliveryOutcome;
use Modules\Glitter\NotificationHub\Exceptions\CorrelationScopeConflictException;
class NotificationCorrelationRegistry
{
    private array $scopes=[];
    public function register(string $notificationClass,string $notificationType,string $extensionType,string $extensionIdentifier,object $notifiable,string $channel,NotificationCorrelationData $data):string
    {
        $key=$this->key($notificationClass,$notificationType,$extensionType,$extensionIdentifier,$notifiable,$channel);if(isset($this->scopes[$key]))throw new CorrelationScopeConflictException;
        $this->scopes[$key]=['correlation'=>$data,'result'=>null,'before'=>false];return $key;
    }
    public function capture(string $hook,string $channel,array $context):void
    {
        $n=$context['notifiable']??null;if(!is_object($n))return;$key=$this->key((string)($context['notification_class']??''),(string)($context['notification_type']??''),(string)($context['extension_type']??''),(string)($context['extension_identifier']??''),$n,$channel);if(!isset($this->scopes[$key]))return;
        if($hook==='core.notification.before_channel_send'){$this->scopes[$key]['before']=true;return;}
        $outcome=$hook==='core.notification.after_channel_send'?CoreDeliveryOutcome::Accepted:CoreDeliveryOutcome::Failed;
        $this->scopes[$key]['result']=new CoreNotificationDeliveryResultData($outcome,$outcome===CoreDeliveryOutcome::Failed?'core_channel_failed':null,$context['error']??null,null,null,null,null,null);
    }
    public function result(string $key):?CoreNotificationDeliveryResultData{return $this->scopes[$key]['result']??null;}
    public function forget(string $key):void{unset($this->scopes[$key]);}
    public function count():int{return count($this->scopes);}
    private function key(string $class,string $type,string $extensionType,string $extensionIdentifier,object $notifiable,string $channel):string{return hash('sha256',implode('|',[$class,$type,$extensionType,$extensionIdentifier,(string)spl_object_id($notifiable),$channel]));}
}
