<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\Services;
use DateTimeImmutable;
use Illuminate\Config\Repository;
use Modules\Glitter\NotificationHub\DTO\CoreNotificationDeliveryResultData;
use Modules\Glitter\NotificationHub\Enums\CoreDeliveryOutcome;
use Modules\Glitter\NotificationHub\Enums\RetryPolicy;
use Modules\Glitter\NotificationHub\Models\NotificationDeliveryAttempt;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use Modules\Glitter\NotificationHub\Services\RetryDecisionService;
use PHPUnit\Framework\TestCase;
class RetryDecisionServiceTest extends TestCase
{
    protected function setUp():void{parent::setUp();app()->instance('config',new Repository(['notification-hub'=>['retry_enabled'=>true,'retry_backoff'=>['standard'=>[60,300],'aggressive'=>[30,60],'none'=>[]]]]));}
    public function test_standard_first_failure_uses_first_delay():void{$p=new NotificationPlan(['retry_policy'=>RetryPolicy::Standard,'attempt_count'=>1,'max_attempts'=>6]);$a=new NotificationDeliveryAttempt(['attempt_number'=>1]);$r=new CoreNotificationDeliveryResultData(CoreDeliveryOutcome::Failed,'timeout','Connection timeout',null,null,null,null,null);$now=new DateTimeImmutable('2026-01-01');$d=(new RetryDecisionService)->decide($p,$a,$r,$now);$this->assertTrue($d->shouldRetry);$this->assertSame($now->modify('+60 seconds')->format(DATE_ATOM),$d->nextAttemptAt?->format(DATE_ATOM));}
    public function test_none_never_retries():void{$p=new NotificationPlan(['retry_policy'=>RetryPolicy::None,'attempt_count'=>1,'max_attempts'=>6]);$a=new NotificationDeliveryAttempt(['attempt_number'=>1]);$r=new CoreNotificationDeliveryResultData(CoreDeliveryOutcome::Failed,'timeout','timeout',null,null,null,null,null);$this->assertFalse((new RetryDecisionService)->decide($p,$a,$r,new DateTimeImmutable)->shouldRetry);}
}
