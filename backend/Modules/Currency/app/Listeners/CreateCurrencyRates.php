<?php

namespace Modules\Currency\Listeners;

use Modules\Currency\Models\CurrencyRate;
use Modules\Setting\Events\SettingSaved;

class CreateCurrencyRates
{
    /**
     * Handle the event.
     *
     * @param SettingSaved $event
     *
     * @return void
     */
    public function handle(SettingSaved $event): void
    {
        CurrencyRate::query()->insert($this->rates());
    }

    /**
     * Get the currency rates.
     *
     * @return array
     */
    private function rates(): array
    {
        $currencyRates = CurrencyRate::query()->pluck('currency');

        return collect(request('supported_currencies'))->reject(function ($currency) use ($currencyRates) {
            return $currencyRates->contains($currency);
        })->map(function ($currency) {
            return [
                'currency' => $currency,
                'rate' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->all();
    }
}
