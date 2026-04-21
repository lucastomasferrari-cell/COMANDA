<?php

namespace Modules\Cart\Models;

use Modules\Support\Casts\AsSerialize;
use Modules\Support\Eloquent\Model;

class Cart extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'data'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "data" => AsSerialize::class,
        ];
    }
}
