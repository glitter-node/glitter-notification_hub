<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\Services;
use DateTimeImmutable;
use Modules\Glitter\NotificationHub\Contracts\Repositories\PlanRepositoryInterface;
use Modules\Glitter\NotificationHub\Enums\PlanStatus;
use Modules\Glitter\NotificationHub\Exceptions\InvalidPlanStateTransitionException;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use Modules\Glitter\NotificationHub\Services\PlanStateTransitionService;
use Modules\Glitter\NotificationHub\Support\DeliveryMetadataSanitizer;
use PHPUnit\Framework\TestCase;
class PlanStateTransitionServiceTest extends TestCase
{
    public function test_processing_to_failed_sets_timestamp_and_truncates_error(): void { $p=new NotificationPlan(['status'=>PlanStatus::Processing]); $r=$this->createMock(PlanRepositoryInterface::class); $r->expects($this->once())->method('updateState')->with($p,PlanStatus::Failed,$this->callback(fn($a)=>$a['failed_at'] instanceof DateTimeImmutable && strlen($a['last_error_message'])===1000))->willReturnCallback(function($p,$s,$a){$p->forceFill([...$a,'status'=>$s]);return $p;}); $result=(new PlanStateTransitionService($r,new DeliveryMetadataSanitizer))->markFailed($p,new DateTimeImmutable,str_repeat('e',101),str_repeat('m',1200)); $this->assertSame(PlanStatus::Failed,$result->status); }
    public function test_terminal_transition_is_rejected(): void { $p=new NotificationPlan(['status'=>PlanStatus::Accepted]); $this->expectException(InvalidPlanStateTransitionException::class); (new PlanStateTransitionService($this->createMock(PlanRepositoryInterface::class),new DeliveryMetadataSanitizer))->markProcessing($p,new DateTimeImmutable); }
}
