<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\Services;
use DateTimeImmutable;
use Illuminate\Config\Repository;
use Modules\Glitter\NotificationHub\Contracts\PlanJobDispatcherInterface;
use Modules\Glitter\NotificationHub\Contracts\Repositories\PlanRepositoryInterface;
use Modules\Glitter\NotificationHub\Services\DuePlanDispatchService;
use PHPUnit\Framework\TestCase;
class DuePlanDispatchServiceTest extends TestCase
{
    public function test_disabled_scheduler_performs_no_repository_or_dispatch_work():void{app()->instance('config',new Repository(['notification-hub'=>['scheduler_enabled'=>false]]));$plans=$this->createMock(PlanRepositoryInterface::class);$plans->expects($this->never())->method('claimDuePlans');$dispatcher=$this->createMock(PlanJobDispatcherInterface::class);$dispatcher->expects($this->never())->method('dispatch');$this->assertTrue((new DuePlanDispatchService($plans,$dispatcher))->dispatchDue(new DateTimeImmutable,50)->disabled);}
}
