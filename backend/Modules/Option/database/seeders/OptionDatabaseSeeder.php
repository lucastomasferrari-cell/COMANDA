<?php

namespace Modules\Option\Database\Seeders;

use App\Forkiva;
use Illuminate\Database\Seeder;

class OptionDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Forkiva::seedDemoData()) {
            $this->call([
                OptionSeeder::class,
            ]);
        }
    }
}
