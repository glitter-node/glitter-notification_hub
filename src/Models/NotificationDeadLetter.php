<?php

namespace Modules\Glitter\NotificationHub\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDeadLetter extends Model
{
    use HasUuids;

    protected $table = 'notification_hub_dead_letters';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'plan_id', 'attempt_id', 'reason_code', 'reason_message', 'failed_at', 'retry_exhausted', 'resolved_at', 'resolved_by', 'resolution_note'];
    protected $hidden = ['reason_message', 'resolution_note'];

    protected function casts(): array
    {
        return ['retry_exhausted' => 'boolean', 'failed_at' => 'datetime', 'resolved_at' => 'datetime', 'resolved_by' => 'integer'];
    }

    public function plan(): BelongsTo { return $this->belongsTo(NotificationPlan::class, 'plan_id'); }
    public function attempt(): BelongsTo { return $this->belongsTo(NotificationDeliveryAttempt::class, 'attempt_id'); }
}
