<?php

namespace Modules\Glitter\NotificationHub\Tests\Unit\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use Modules\Glitter\NotificationHub\Exceptions\PlanRecipientResolutionException;
use Modules\Glitter\NotificationHub\Models\NotificationPlan;
use Modules\Glitter\NotificationHub\Services\PlanRecipientResolver;
use PHPUnit\Framework\TestCase;

class PlanRecipientResolverTest extends TestCase
{
    public function test_guest_without_encrypted_address_is_blocked_without_query(): void
    {
        $users = $this->createMock(UserRepositoryInterface::class);
        $users->expects($this->never())->method('findById');
        $this->expectException(PlanRecipientResolutionException::class);
        (new PlanRecipientResolver($users))->resolve(new NotificationPlan(['recipient_type' => 'guest']));
    }

    public function test_role_is_not_expanded_by_the_adapter_boundary(): void
    {
        $this->expectException(PlanRecipientResolutionException::class);
        (new PlanRecipientResolver($this->createMock(UserRepositoryInterface::class)))
            ->resolve(new NotificationPlan(['recipient_type' => 'role']));
    }
}
