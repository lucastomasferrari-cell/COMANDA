<?php

namespace Modules\Currency\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Currency\Services\CurrencyRateExchanger;
use Modules\Support\Eloquent\Model;
use Modules\Support\Traits\HasFilters;
use Modules\Support\Traits\HasSortBy;

/**
 * @property int $id
 * @property string $currency
 * @property float $rate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class CurrencyRate extends Model
{
    use HasSortBy, HasFilters;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'currency',
        'rate'
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    public static function booted(): void
    {
        static::saved(function ($currencyRate) {
            Cache::forget(md5("currency_rate.$currencyRate->currency"));
        });
    }

    /**
     * Refresh all supported currencies exchange rate.
     *
     * @param CurrencyRateExchanger $exchanger
     *
     * @return void
     */
    public static function refreshRates(CurrencyRateExchanger $exchanger): void
    {
        $fromCurrency = setting('default_currency');
        foreach (setting('supported_currencies') as $toCurrency) {
            $rate = $exchanger->exchange($fromCurrency, $toCurrency);
            static::where('currency', $toCurrency)->first()->update(['rate' => $rate]);
        }
    }

    /**
     * Get currency rate for the given currency.
     *
     * Si no existe la fila correspondiente (caso típico: tabla sin seeder
     * corrido, o moneda agregada a setting sin crear el rate), retorna 1.0
     * con un warning en logs en vez de propagar null. Antes con return
     * type strict int|float, este path tiraba un "TypeError: must be of
     * type int|float, null returned" y bloqueaba el flujo completo del POS.
     * El warning deja rastro para que el seeder/admin lo corrija.
     *
     * @param string $currency
     * @return float
     */
    public static function for(string $currency): float
    {
        $rate = Cache::rememberForever(md5("currency_rate.$currency"), function () use ($currency) {
            return static::where('currency', $currency)->value('rate');
        });

        if ($rate === null) {
            Log::warning("CurrencyRate::for({$currency}) returned null — no row found, falling back to 1.0. Run CurrencyDatabaseSeeder or create the row manually.");
            return 1.0;
        }

        return (float) $rate;
    }

    /**
     * Get default allowed filters
     *
     * @return array
     */
    public function allowedFilterKeys(): array
    {
        return [
            'search'
        ];
    }

    /**
     * Scope a query to search across all fields.
     *
     * @param Builder $query
     * @param string $value
     * @return void
     */
    public function scopeSearch(Builder $query, string $value): void
    {
        $query->where(function (Builder $query) use ($value) {
            $query->like('currency', $value)->orLike('rate', $value);
        });
    }

    /** @inheritDoc */
    protected function getSortableAttributes(): array
    {
        return [
            'currency',
            'rate'
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rate' => 'float',
        ];
    }
}
