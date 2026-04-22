<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Translation\Traits\Translatable;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $applies_to
 * @property bool $requires_manager_approval
 * @property bool $is_active
 * @property int $order
 */
class VoidReason extends Model
{
    use Translatable;

    protected $fillable = [
        'code',
        'name',
        'applies_to',
        'requires_manager_approval',
        'is_active',
        'order',
    ];

    protected array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'requires_manager_approval' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
