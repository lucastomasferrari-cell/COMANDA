<?php

namespace Modules\Payment\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Payment\Enums\PaymentMethod as PaymentMethodEnum;
use Modules\Support\Eloquent\Model;
use Modules\Support\Traits\HasActiveStatus;
use Modules\Support\Traits\HasFilters;
use Modules\Support\Traits\HasOrder;
use Modules\Support\Traits\HasSortBy;

/**
 * Payment method configurado en el admin. Se distingue del enum
 * Payment\Enums\PaymentMethod (que es el TIPO base del payment).
 * Nombrado PaymentMethodItem para evitar colisión de namespaces.
 *
 * @property int $id
 * @property string $name
 * @property PaymentMethodEnum $type
 * @property bool $impacts_cash
 * @property bool $is_active
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class PaymentMethodItem extends Model
{
    use SoftDeletes, HasActiveStatus, HasFilters, HasSortBy, HasOrder;

    protected $table = 'payment_methods';

    protected $fillable = [
        'name',
        'type',
        'impacts_cash',
        'is_active',
        'order',
    ];

    public function allowedFilterKeys(): array
    {
        return [
            'search',
            'type',
            self::ACTIVE_COLUMN_NAME,
        ];
    }

    public function scopeSearch(Builder $query, string $value): void
    {
        $query->like('name', $value);
    }

    protected function getSortableAttributes(): array
    {
        return ['name', 'type', 'order', self::ACTIVE_COLUMN_NAME];
    }

    protected function casts(): array
    {
        return [
            'type' => PaymentMethodEnum::class,
            'impacts_cash' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
