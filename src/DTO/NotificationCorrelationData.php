<?php
namespace Modules\Glitter\NotificationHub\DTO;
final readonly class NotificationCorrelationData
{
    public function __construct(public string $planId, public string $attemptId, public string $correlationId) {}
    public function toArray(): array { return ['plan_id'=>$this->planId,'attempt_id'=>$this->attemptId,'correlation_id'=>$this->correlationId]; }
}
