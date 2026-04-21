<?php

namespace Modules\SeatingPlan\Database\Seeders;

use App\Forkiva;
use Illuminate\Database\Seeder;

class SeatingPlanDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Forkiva::seedDemoData()) {
            $this->call([
                FloorSeeder::class,
                ZoneSeeder::class,
                TableSeeder::class,
            ]);
        }
    }
}
