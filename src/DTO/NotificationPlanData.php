<?php

namespace Modules\Glitter\NotificationHub\DTO;

use DateTimeInterface;
use Modules\Glitter\NotificationHub\Enums\RetryPolicy;

final readonly class NotificationPlanData
{
    /**
     * 스냅샷 배열에는 전달에 필요한 최소 정보만 포함해야 하며 비밀값이나
     * 게스트 이메일 원문을 장기 보관해서는 안 됩니다.
     *
     * @param array<string, mixed> $recipientSnapshot
     * @param array<string, mixed> $data
     * @param array<string, mixed> $context
     * @param array<string, mixed> $policySnapshot
     */
    public function __construct(
        public string $definitionType,
        public string $hookPrefix,
        public string $extensionType,
        public string $extensionIdentifier,
        public ?string $sourceType,
        public string|int|null $sourceId,
        public string $recipientType,
        public ?int $recipientId,
        public ?string $recipientIdentifierHash,
        public array $recipientSnapshot,
        public string $channel,
        public array $data,
        public array $context,
        public array $policySnapshot,
        public string $idempotencyKey,
        public RetryPolicy $retryPolicy,
        public int $maxAttempts,
        public ?DateTimeInterface $scheduledAt,
        public ?DateTimeInterface $expiresAt,
        public ?int $createdBy,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'definition_type' => $this->definitionType,
            'hook_prefix' => $this->hookPrefix,
            'extension_type' => $this->extensionType,
            'extension_identifier' => $this->extensionIdentifier,
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
            'recipient_type' => $this->recipientType,
            'recipient_id' => $this->recipientId,
            'recipient_identifier_hash' => $this->recipientIdentifierHash,
            'recipient_snapshot' => $this->recipientSnapshot,
            'channel' => $this->channel,
            'data' => $this->data,
            'context' => $this->context,
            'policy_snapshot' => $this->policySnapshot,
            'retry_policy' => $this->retryPolicy->value,
            'max_attempts' => $this->maxAttempts,
            'scheduled_at' => $this->scheduledAt,
            'expires_at' => $this->expiresAt,
            'created_by' => $this->createdBy,
        ];
    }
}
