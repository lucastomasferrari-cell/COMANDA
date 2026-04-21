<?php

namespace Modules\Currency\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Currency\Models\CurrencyRate;
use Modules\Setting\Models\Setting;

class CurrencyDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CurrencyRate::query()
            ->updateOrCreate(
                ['currency' => Setting::get('default_currency')],
                ['rate' => 1]
            );
    }
}
