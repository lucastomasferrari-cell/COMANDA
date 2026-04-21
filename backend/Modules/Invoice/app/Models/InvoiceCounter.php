<?php

namespace Modules\Invoice\Models;

use Carbon\Carbon;
use Modules\Support\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $cr_number
 * @property int $last_counter
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class InvoiceCounter extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'cr_number',
        'last_counter',
    ];
}
