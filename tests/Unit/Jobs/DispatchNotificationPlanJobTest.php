<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\Jobs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Glitter\NotificationHub\Contracts\Repositories\PlanRepositoryInterface;
use Modules\Glitter\NotificationHub\Jobs\DispatchNotificationPlanJob;
use PHPUnit\Framework\TestCase;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use Modules\Glitter\NotificationHub\DTO\NotificationResultData;
use Modules\Glitter\NotificationHub\Enums\PlanStatus;
use Modules\Glitter\NotificationHub\Services\NotificationDeliveryOrchestrator;
use ReflectionClass;
class DispatchNotificationPlanJobTest extends TestCase {
 public function test_job_is_single_try_and_contains_only_identifiers():void{$r=new ReflectionClass(DispatchNotificationPlanJob::class);$this->assertTrue($r->implementsInterface(ShouldQueue::class));$params=array_map(fn($p)=>$p->getName(),$r->getConstructor()->getParameters());$this->assertSame(['planId','claimToken'],$params);}
 public function test_valid_claim_is_consumed_before_orchestration():void{$plan=new NotificationPlan(['status'=>'processing']);$plan->id='plan';$plan->correlation_id='correlation';$repo=$this->createMock(PlanRepositoryInterface::class);$repo->expects($this->once())->method('consumeClaim')->with('plan','token',$this->isInstanceOf(\DateTimeInterface::class))->willReturn($plan);$orchestrator=$this->createMock(NotificationDeliveryOrchestrator::class);$orchestrator->expects($this->once())->method('deliverProcessingPlan')->with($plan)->willReturn(new NotificationResultData('plan','correlation',PlanStatus::Processing,false));(new DispatchNotificationPlanJob('plan','token'))->handle($repo,$orchestrator);}
 public function test_stale_claim_is_a_no_op():void{$repo=$this->createMock(PlanRepositoryInterface::class);$repo->method('consumeClaim')->willReturn(null);$orchestrator=$this->createMock(NotificationDeliveryOrchestrator::class);$orchestrator->expects($this->never())->method('deliverProcessingPlan');(new DispatchNotificationPlanJob('plan','old-token'))->handle($repo,$orchestrator);}
}
