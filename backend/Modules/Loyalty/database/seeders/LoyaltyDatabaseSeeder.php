<?php

namespace Modules\Loyalty\Database\Seeders;

use App\Forkiva;
use Illuminate\Database\Seeder;

class LoyaltyDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Forkiva::seedDemoData()) {
            $this->call([
//                LoyaltyProgramSeeder::class,
//                LoyaltyTierSeeder::class,
//                LoyaltyCustomerSeeder::class,
//                LoyaltyTransactionSeeder::class,
//                LoyaltyPromotionSeeder::class
                LoyaltyDemoSeeder::class,
                LoyaltyRewardSeeder::class,
            ]);
        }
    }
}
