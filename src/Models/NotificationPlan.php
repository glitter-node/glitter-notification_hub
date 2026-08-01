<?php

namespace Modules\Glitter\NotificationHub\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Glitter\NotificationHub\Enums\PlanStatus;
use Modules\Glitter\NotificationHub\Enums\RetryPolicy;

class NotificationPlan extends Model
{
    use HasUuids;

    protected $table = 'notification_hub_plans';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'correlation_id',
        'definition_type',
        'hook_prefix',
        'extension_type',
        'extension_identifier',
        'source_type',
        'source_id',
        'recipient_type',
        'recipient_id',
        'recipient_identifier_hash',
        'recipient_identity_hash',
        'recipient_snapshot',
        'channel',
        'data',
        'context',
        'policy_snapshot',
        'idempotency_hash',
        'status',
        'claimed_from_status',
        'claim_token',
        'claimed_at',
        'retry_policy',
        'attempt_count',
        'max_attempts',
        'scheduled_at',
        'next_attempt_at',
        'processing_started_at',
        'accepted_at',
        'failed_at',
        'cancelled_at',
        'expires_at',
        'last_error_code',
        'last_error_message',
        'created_by',
        'retry_of_plan_id', 'manual_retry_reason', 'manual_retry_by', 'cancel_reason', 'cancelled_by',
    ];

    protected $hidden = [
        'recipient_identifier_hash',
        'recipient_identity_hash',
        'recipient_snapshot',
        'data',
        'context',
        'policy_snapshot',
        'idempotency_hash',
        'claim_token',
        'last_error_message',
        'manual_retry_reason', 'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'recipient_id' => 'integer',
            'recipient_snapshot' => 'array',
            'data' => 'array',
            'context' => 'array',
            'policy_snapshot' => 'array',
            'status' => PlanStatus::class,
            'claimed_from_status' => PlanStatus::class,
            'claimed_at' => 'datetime',
            'retry_policy' => RetryPolicy::class,
            'attempt_count' => 'integer',
            'max_attempts' => 'integer',
            'scheduled_at' => 'datetime',
            'next_attempt_at' => 'datetime',
            'processing_started_at' => 'datetime',
            'accepted_at' => 'datetime',
            'failed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'expires_at' => 'datetime',
            'created_by' => 'integer',
            'manual_retry_by' => 'integer',
            'cancelled_by' => 'integer',
        ];
    }

    /** @return HasMany<NotificationDeliveryAttempt, $this> */
    public function attempts(): HasMany
    {
        return $this->hasMany(NotificationDeliveryAttempt::class, 'plan_id');
    }
    public function deadLetter(): HasOne { return $this->hasOne(NotificationDeadLetter::class,'plan_id'); }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            PlanStatus::Accepted,
            PlanStatus::Skipped,
            PlanStatus::Failed,
            PlanStatus::Cancelled,
            PlanStatus::Expired,
        ], true);
    }
}
