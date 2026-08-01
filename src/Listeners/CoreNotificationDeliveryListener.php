<?php
namespace Modules\Glitter\NotificationHub\Listeners;
use App\Contracts\Extension\HookListenerInterface;
use Modules\Glitter\NotificationHub\Support\NotificationCorrelationRegistry;
class CoreNotificationDeliveryListener implements HookListenerInterface
{
    public function __construct(private readonly NotificationCorrelationRegistry $registry) {}
    public static function getSubscribedHooks():array{return ['core.notification.before_channel_send'=>['method'=>'before','priority'=>5,'sync'=>true],'core.notification.after_channel_send'=>['method'=>'after','priority'=>5,'sync'=>true],'core.notification.channel_send_failed'=>['method'=>'failed','priority'=>5,'sync'=>true]];}
    public function handle(...$args):void{}
    public function before(string $channel,array $context):void{$this->registry->capture('core.notification.before_channel_send',$channel,$context);}
    public function after(string $channel,array $context):void{$this->registry->capture('core.notification.after_channel_send',$channel,$context);}
    public function failed(string $channel,array $context):void{$this->registry->capture('core.notification.channel_send_failed',$channel,$context);}
}
