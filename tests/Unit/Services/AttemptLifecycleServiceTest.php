<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\Services;
use Carbon\CarbonImmutable;
use Modules\Glitter\NotificationHub\Contracts\Repositories\AttemptRepositoryInterface;
use Modules\Glitter\NotificationHub\Enums\AttemptStatus;
use Modules\Glitter\NotificationHub\Enums\PlanStatus;
use Modules\Glitter\NotificationHub\Exceptions\InvalidAttemptStateTransitionException;
use Modules\Glitter\NotificationHub\Models\NotificationDeliveryAttempt;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use Modules\Glitter\NotificationHub\Services\AttemptLifecycleService;
use Modules\Glitter\NotificationHub\Support\DeliveryMetadataSanitizer;
use PHPUnit\Framework\TestCase;
class AttemptLifecycleServiceTest extends TestCase
{
    public function test_start_and_accept_calculate_duration_and_sanitize_metadata(): void { $p=new NotificationPlan(['status'=>PlanStatus::Processing]); $a=new NotificationDeliveryAttempt(['status'=>AttemptStatus::Pending]); $r=$this->createMock(AttemptRepositoryInterface::class); $r->method('createNextAttempt')->willReturn($a); $r->method('updateState')->willReturnCallback(function($a,$s,$v){$a->forceFill([...$v,'status'=>$s]);return $a;}); $s=new AttemptLifecycleService($r,new DeliveryMetadataSanitizer); $started=CarbonImmutable::parse('2026-01-01 00:00:00'); $a=$s->start($p,$started); $a=$s->markAccepted($a,$started->addMilliseconds(1250),null,202,['Authorization'=>'secret']); $this->assertSame(AttemptStatus::Accepted,$a->status); $this->assertSame(1250,$a->duration_ms); $this->assertSame('[REDACTED]',$a->response_metadata['Authorization']); }
    public function test_terminal_attempt_cannot_change(): void { $a=new NotificationDeliveryAttempt(['status'=>AttemptStatus::Failed]); $this->expectException(InvalidAttemptStateTransitionException::class); (new AttemptLifecycleService($this->createMock(AttemptRepositoryInterface::class),new DeliveryMetadataSanitizer))->cancel($a,new CarbonImmutable); }
}
