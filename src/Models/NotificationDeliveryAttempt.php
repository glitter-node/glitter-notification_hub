<?php

namespace Modules\Glitter\NotificationHub\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Glitter\NotificationHub\Enums\AttemptStatus;

class NotificationDeliveryAttempt extends Model
{
    use HasUuids;

    protected $table = 'notification_hub_delivery_attempts';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'plan_id',
        'attempt_number',
        'status',
        'provider',
        'started_at',
        'completed_at',
        'duration_ms',
        'retryable',
        'next_retry_at',
        'error_category',
        'error_code',
        'error_message',
        'provider_message_id',
        'response_status',
        'request_metadata',
        'response_metadata',
        'core_notification_log_id',
    ];

    /**
     * Metadata에는 Authorization, Cookie, secret 또는 전체 본문을 저장하지 않습니다.
     */
    protected $hidden = [
        'error_message',
        'provider_message_id',
        'request_metadata',
        'response_metadata',
    ];

    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'status' => AttemptStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'duration_ms' => 'integer',
            'retryable' => 'boolean',
            'next_retry_at' => 'datetime',
            'response_status' => 'integer',
            'request_metadata' => 'array',
            'response_metadata' => 'array',
            'core_notification_log_id' => 'integer',
        ];
    }

    /** @return BelongsTo<NotificationPlan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(NotificationPlan::class, 'plan_id');
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            AttemptStatus::Accepted,
            AttemptStatus::Failed,
            AttemptStatus::Cancelled,
        ], true);
    }
}
