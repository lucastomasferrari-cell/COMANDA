<?php

namespace Modules\AuditLog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\User\Models\User;

class PendingApproval extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'related_model',
        'related_id',
        'details',
        'status',
        'reviewed_by',
        'reviewed_at',
        'reviewer_notes',
        'expires_at',
    ];

    protected $casts = [
        'details' => 'array',
        'reviewed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function related(): MorphTo
    {
        return $this->morphTo(null, 'related_model', 'related_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
