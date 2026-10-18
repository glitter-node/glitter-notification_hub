<?php
namespace Modules\Glitter\NotificationHub\DTO\Admin;
use DateTimeImmutable;
use Modules\Glitter\NotificationHub\Enums\RetryPolicy;
final readonly class ManualRetryData { public function __construct(public ?string $requestId,public string $reason,public ?DateTimeImmutable $scheduledAt,public RetryPolicy $retryPolicy,public int $maxAttempts,public ?int $actorId){} public static function fromArray(array $v,?int $actorId):self{return new self(($v['request_id']??null)?:null,$v['reason'],isset($v['scheduled_at'])?new DateTimeImmutable($v['scheduled_at']):null,RetryPolicy::from($v['retry_policy']??'none'),(int)($v['max_attempts']??1),$actorId);} }
