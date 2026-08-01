<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\DTO;
use Modules\Glitter\NotificationHub\DTO\PlanCreationResultData;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use PHPUnit\Framework\TestCase;
class PlanCreationResultDataTest extends TestCase
{
    public function test_internal_result_serializes_only_identifier(): void { $p=new NotificationPlan; $p->id='uuid'; $d=new PlanCreationResultData($p,true); $this->assertTrue((new \ReflectionClass($d))->isReadOnly()); $this->assertSame(['plan_id'=>'uuid','is_duplicate'=>true],$d->toArray()); }
}
