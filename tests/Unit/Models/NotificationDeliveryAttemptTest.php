<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\Models;

use Modules\Glitter\NotificationHub\Enums\AttemptStatus;
use Modules\Glitter\NotificationHub\Models\NotificationDeliveryAttempt;
use PHPUnit\Framework\TestCase;

class NotificationDeliveryAttemptTest extends TestCase
{
    public function test_model_metadata_casts_and_relationship_are_declared(): void
    {
        $attempt = new NotificationDeliveryAttempt;

        $this->assertSame('notification_hub_delivery_attempts', $attempt->getTable());
        $this->assertFalse($attempt->getIncrementing());
        $this->assertSame(AttemptStatus::class, $attempt->getCasts()['status']);
        $this->assertSame('array', $attempt->getCasts()['request_metadata']);
        $this->assertSame(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            (new \ReflectionMethod($attempt, 'plan'))->getReturnType()?->getName(),
        );
        $this->assertContains('request_metadata', $attempt->getHidden());
    }

    public function test_terminal_status_helper_has_no_transition_side_effects(): void
    {
        $attempt = new NotificationDeliveryAttempt(['status' => AttemptStatus::Failed]);
        $this->assertTrue($attempt->isTerminal());

        $attempt->status = AttemptStatus::Processing;
        $this->assertFalse($attempt->isTerminal());
    }
}
