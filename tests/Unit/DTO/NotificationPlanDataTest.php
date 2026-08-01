<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\DTO;

use Carbon\CarbonImmutable;
use Modules\Glitter\NotificationHub\DTO\NotificationPlanData;
use Modules\Glitter\NotificationHub\Enums\RetryPolicy;
use PHPUnit\Framework\TestCase;

class NotificationPlanDataTest extends TestCase
{
    public function test_it_is_readonly_and_exports_persistence_fields(): void
    {
        $scheduledAt = CarbonImmutable::parse('2026-08-02 09:00:00');
        $data = new NotificationPlanData(
            definitionType: 'booking.confirmed',
            hookPrefix: 'booking.confirmed',
            extensionType: 'module',
            extensionIdentifier: 'sirsoft-booking',
            sourceType: 'booking',
            sourceId: 42,
            recipientType: 'user',
            recipientId: 7,
            recipientIdentifierHash: null,
            recipientSnapshot: ['locale' => 'ko'],
            channel: 'mail',
            data: ['booking_id' => 42],
            context: ['locale' => 'ko'],
            policySnapshot: ['quiet_hours' => false],
            idempotencyKey: 'booking:42',
            retryPolicy: RetryPolicy::Standard,
            maxAttempts: 3,
            scheduledAt: $scheduledAt,
            expiresAt: null,
            createdBy: 7,
        );

        $this->assertTrue((new \ReflectionClass($data))->isReadOnly());
        $this->assertSame('standard', $data->toArray()['retry_policy']);
        $this->assertSame($scheduledAt, $data->toArray()['scheduled_at']);
        $this->assertSame(42, $data->toArray()['source_id']);
    }
}
