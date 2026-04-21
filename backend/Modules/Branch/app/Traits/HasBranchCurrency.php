<?php

namespace Modules\Branch\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property string $currency
 */
trait HasBranchCurrency
{
    /**
     * Get currency
     *
     * @return Attribute
     */
    public function currency(): Attribute
    {
        return Attribute::get(
            fn() => $this->relationLoaded("branch")
                ? ($this->branch?->currency ?: setting('default_currency'))
                : setting('default_currency')
        );
    }
}
