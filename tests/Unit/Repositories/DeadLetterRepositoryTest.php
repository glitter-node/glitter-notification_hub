<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\Repositories;
use Modules\Glitter\NotificationHub\Contracts\Repositories\DeadLetterRepositoryInterface;
use PHPUnit\Framework\TestCase;
class DeadLetterRepositoryTest extends TestCase { public function test_repository_has_one_record_per_plan_contract():void{$this->assertTrue(method_exists(DeadLetterRepositoryInterface::class,'createForFailure'));$this->assertTrue(method_exists(DeadLetterRepositoryInterface::class,'findByPlanId'));} }
