<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\Services;
use Carbon\CarbonImmutable;
use Modules\Glitter\NotificationHub\Contracts\Repositories\PlanRepositoryInterface;
use Modules\Glitter\NotificationHub\DTO\NotificationPlanData;
use Modules\Glitter\NotificationHub\DTO\PlanCreationResultData;
use Modules\Glitter\NotificationHub\Enums\PlanStatus;
use Modules\Glitter\NotificationHub\Enums\RetryPolicy;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use Modules\Glitter\NotificationHub\Services\NotificationHubService;
use Modules\Glitter\NotificationHub\Services\PlanStateTransitionService;
use PHPUnit\Framework\TestCase;
class NotificationHubServiceTest extends TestCase
{
    protected function tearDown(): void { CarbonImmutable::setTestNow(); parent::tearDown(); }
    public function test_duplicate_returns_existing_without_transition(): void { $p=$this->plan(PlanStatus::Accepted); $r=$this->createMock(PlanRepositoryInterface::class); $r->method('createOrFindDuplicate')->willReturn(new PlanCreationResultData($p,true)); $result=(new NotificationHubService($r,$this->createMock(PlanStateTransitionService::class)))->createPlan($this->data()); $this->assertTrue($result->isDuplicate); $this->assertSame(PlanStatus::Accepted,$result->status); }
    public function test_future_plan_is_initialized_as_scheduled(): void { CarbonImmutable::setTestNow('2026-01-01'); $p=$this->plan(PlanStatus::Pending); $r=$this->createMock(PlanRepositoryInterface::class); $r->method('createOrFindDuplicate')->willReturn(new PlanCreationResultData($p,false)); $r->expects($this->once())->method('updateState')->with($p,PlanStatus::Scheduled,$this->anything())->willReturnCallback(function($p,$s){$p->status=$s;return $p;}); $result=(new NotificationHubService($r,$this->createMock(PlanStateTransitionService::class)))->createPlan($this->data(CarbonImmutable::now()->addHour())); $this->assertSame(PlanStatus::Scheduled,$result->status); }
    private function plan(PlanStatus $s): NotificationPlan { $p=new NotificationPlan(['status'=>$s,'correlation_id'=>'correlation']);$p->id='plan';return $p; }
    private function data(?CarbonImmutable $scheduled=null): NotificationPlanData { return new NotificationPlanData('x','x','module','m',null,null,'user',1,null,[],'mail',[],[],[],'key',RetryPolicy::None,1,$scheduled,null,null); }
}
