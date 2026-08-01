<?php

namespace Modules\Glitter\NotificationHub\DTO;

final readonly class CoreNotificationDeliveryData
{
    /** Runtime-only adapter DTO. It contains a notifiable object and must not be queued or serialized. */
    public function __construct(
        public string $planId,
        public string $attemptId,
        public string $correlationId,
        public string $definitionType,
        public string $hookPrefix,
        public string $extensionType,
        public string $extensionIdentifier,
        public string $channel,
        public array $data,
        public object $notifiable,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['plan_id'=>$this->planId,'attempt_id'=>$this->attemptId,'correlation_id'=>$this->correlationId,'definition_type'=>$this->definitionType,'hook_prefix'=>$this->hookPrefix,'extension_type'=>$this->extensionType,'extension_identifier'=>$this->extensionIdentifier,'channel'=>$this->channel,'data'=>$this->data];
    }
}
