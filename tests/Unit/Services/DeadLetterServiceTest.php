<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\Services;
use DateTimeImmutable;
use Illuminate\Config\Repository;
use Modules\Glitter\NotificationHub\Contracts\Repositories\DeadLetterRepositoryInterface;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use Modules\Glitter\NotificationHub\Services\DeadLetterService;
use Modules\Glitter\NotificationHub\Support\DeliveryMetadataSanitizer;
use PHPUnit\Framework\TestCase;
class DeadLetterServiceTest extends TestCase
{
    public function test_disabled_dead_letter_does_not_write():void{app()->instance('config',new Repository(['notification-hub'=>['dead_letter_enabled'=>false]]));$repo=$this->createMock(DeadLetterRepositoryInterface::class);$repo->expects($this->never())->method('createForFailure');$this->assertNull((new DeadLetterService($repo,new DeliveryMetadataSanitizer))->record(new NotificationPlan,null,'failed','secret',new DateTimeImmutable,false));}
}
