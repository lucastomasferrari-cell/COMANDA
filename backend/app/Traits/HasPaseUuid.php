<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Agrega una columna pase_uuid (CHAR(26) ULID) a un modelo para que
 * pueda referenciarse desde PASE (sistema central multi-tenant) sin
 * exponer los bigint IDs secuenciales. El ULID se genera en el hook
 * `creating` si no fue seteado a mano antes.
 *
 * Los ULIDs son:
 *  - ordenables por tiempo (26 chars, prefijo timestamp).
 *  - unique con alta probabilidad cosmica.
 *  - seguros para URLs y legibles.
 *
 * El modelo que use este trait necesita la columna pase_uuid en su
 * tabla y tenerla en $fillable (o $guarded=[]).
 *
 * @property string|null $pase_uuid
 */
trait HasPaseUuid
{
    public static function bootHasPaseUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->pase_uuid)) {
                $model->pase_uuid = (string) Str::ulid();
            }
        });
    }
}
