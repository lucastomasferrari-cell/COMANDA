<?php

namespace Modules\AuditLog\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;
use Modules\User\Models\User;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $auditable_type
 * @property int $auditable_id
 * @property string $action
 * @property string|null $reason
 * @property array|null $old_values
 * @property array|null $new_values
 * @property array|null $metadata
 * @property int|null $approved_by
 * @property int|null $parent_id
 * @property bool $is_fiscal
 * @property Carbon $created_at
 * @property-read User|null $user
 * @property-read User|null $approver
 * @property-read AuditLog|null $parent
 */
class AuditLog extends Model
{
    /**
     * append-only: no updated_at, Laravel ignora el attr al crear.
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'action',
        'reason',
        'old_values',
        'new_values',
        'metadata',
        'approved_by',
        'parent_id',
        'is_fiscal',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'is_fiscal' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Bloqueamos updates una vez persistido. save() se llama al crear
     * (exists=false) y al actualizar (exists=true). Solo dejamos pasar
     * el create. Para deletes administrativos (cleanup), uso query
     * builder directo (DB::delete) en el command; el Model queda
     * inmutable desde cualquier otro caller.
     */
    public function save(array $options = [])
    {
        if ($this->exists) {
            throw new LogicException(
                'AuditLog is append-only. Use AuditLogger::log() for new entries.'
            );
        }

        return parent::save($options);
    }

    public function delete(): bool
    {
        throw new LogicException(
            'AuditLog delete is forbidden. Use the audit:cleanup command for archival.'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
