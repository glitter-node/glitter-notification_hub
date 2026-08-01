<?php
namespace Modules\Glitter\NotificationHub\DTO;
final readonly class ResolvedPlanRecipientData
{
    public function __construct(public object $notifiable, public string $recipientType, public ?int $recipientUserId, public ?string $recipientIdentifier, public ?string $locale, public bool $isGuest) {}
    public function toArray(): array { return ['recipient_type'=>$this->recipientType,'recipient_user_id'=>$this->recipientUserId,'recipient_identifier'=>$this->recipientIdentifier,'locale'=>$this->locale,'is_guest'=>$this->isGuest]; }
}
