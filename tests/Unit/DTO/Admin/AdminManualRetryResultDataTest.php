<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\DTO\Admin;
use Modules\Glitter\NotificationHub\DTO\Admin\AdminManualRetryResultData;use Modules\Glitter\NotificationHub\DTO\NotificationResultData;use Modules\Glitter\NotificationHub\Enums\PlanStatus;use PHPUnit\Framework\TestCase;
class AdminManualRetryResultDataTest extends TestCase{public function test_response_contains_effective_request_id():void{$dto=new AdminManualRetryResultData(new NotificationResultData('p1','c1',PlanStatus::Pending,false),'00000000-0000-4000-8000-000000000001');$this->assertSame('00000000-0000-4000-8000-000000000001',$dto->toArray()['request_id']);}}
