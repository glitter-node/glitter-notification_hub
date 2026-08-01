<?php
namespace Modules\Glitter\NotificationHub\Tests\Unit\DTO;
use DateTimeImmutable;
use Modules\Glitter\NotificationHub\DTO\RetryDecisionData;
use PHPUnit\Framework\TestCase;
class RetryDecisionDataTest extends TestCase { public function test_serializes_decision(): void { $d=new RetryDecisionData(true,new DateTimeImmutable('2026-01-01T00:01:00+00:00'));$this->assertTrue($d->toArray()['should_retry']);$this->assertSame('2026-01-01T00:01:00+00:00',$d->toArray()['next_attempt_at']); } }
