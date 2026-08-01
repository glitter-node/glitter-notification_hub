<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\DTO;
use Modules\Glitter\NotificationHub\DTO\DuePlanDispatchResultData;
use PHPUnit\Framework\TestCase;
class DuePlanDispatchResultDataTest extends TestCase { public function test_exposes_counters(): void { $d=new DuePlanDispatchResultData(2,1,1);$this->assertSame(2,$d->eligibleCount);$this->assertSame(1,$d->toArray()['dispatchedCount']); } }
