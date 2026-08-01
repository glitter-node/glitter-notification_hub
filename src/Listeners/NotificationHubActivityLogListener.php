<?php
namespace Modules\Glitter\NotificationHub\Listeners;use App\ActivityLog\Traits\ResolvesActivityLogType;use App\Contracts\Extension\HookListenerInterface;
class NotificationHubActivityLogListener implements HookListenerInterface
{
 use ResolvesActivityLogType;
 public static function getSubscribedHooks():array{return ['glitter-notification_hub.plan.after_cancel'=>['method'=>'cancel','priority'=>20],'glitter-notification_hub.plan.after_manual_retry'=>['method'=>'planRetry','priority'=>20],'glitter-notification_hub.dead_letter.after_resolve'=>['method'=>'resolve','priority'=>20],'glitter-notification_hub.dead_letter.after_manual_retry'=>['method'=>'deadLetterRetry','priority'=>20]];}
 public function handle(...$args):void{} public function cancel(array $c):void{$this->write('plan.cancel',$c);} public function planRetry(array $c):void{$this->write('plan.manual_retry',$c);} public function resolve(array $c):void{$this->write('dead_letter.resolve',$c);} public function deadLetterRetry(array $c):void{$this->write('dead_letter.manual_retry',$c);}
 private function write(string $action,array $context):void{$this->logActivity('glitter-notification_hub.'.$action,['description_key'=>'glitter-notification_hub::activity.'.$action,'description_params'=>array_filter(['plan_id'=>$context['plan_id']??null,'new_plan_id'=>$context['new_plan_id']??null,'dead_letter_id'=>$context['dead_letter_id']??null]),'properties'=>array_filter(['actor_id'=>$context['actor_id']??null,'reason_code'=>$context['reason_code']??null])]);}
}
