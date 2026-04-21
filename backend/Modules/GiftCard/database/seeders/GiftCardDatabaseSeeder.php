<?php

namespace Modules\GiftCard\Database\Seeders;

use App\Forkiva;
use Illuminate\Database\Seeder;

class GiftCardDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Forkiva::seedDemoData()) {
            $this->call([
                GiftCardBatchSeeder::class,
                GiftCardSeeder::class,
                GiftCardTransactionSeeder::class,
            ]);
        }
    }
}
