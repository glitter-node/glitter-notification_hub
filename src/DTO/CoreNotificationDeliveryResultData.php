<?php

namespace Modules\Glitter\NotificationHub\DTO;
use Modules\Glitter\NotificationHub\Enums\CoreDeliveryOutcome;

final readonly class CoreNotificationDeliveryResultData
{
    /** @param array<string, mixed>|null $responseMetadata */
    public function __construct(
        public CoreDeliveryOutcome $outcome,
        public ?string $reasonCode,
        public ?string $reasonMessage,
        public ?string $provider,
        public ?string $providerMessageId,
        public ?int $responseStatus,
        public ?array $responseMetadata,
        public ?int $coreNotificationLogId,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['outcome'=>$this->outcome->value,'reason_code'=>$this->reasonCode,'reason_message'=>$this->reasonMessage,'provider'=>$this->provider,'provider_message_id'=>$this->providerMessageId,'response_status'=>$this->responseStatus,'response_metadata'=>$this->responseMetadata,'core_notification_log_id'=>$this->coreNotificationLogId];
    }
}
