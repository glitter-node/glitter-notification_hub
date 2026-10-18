<?php
namespace Modules\Glitter\NotificationHub\DTO;
final readonly class CoreNotificationPolicyResultData
{
    public function __construct(public bool $allowed, public ?string $reasonCode = null, public ?string $reasonMessage = null) {}
    public function toArray(): array { return ['allowed'=>$this->allowed,'reason_code'=>$this->reasonCode,'reason_message'=>$this->reasonMessage]; }
}
