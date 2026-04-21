<?php

namespace Modules\Product\Database\Seeders;

use App\Forkiva;
use Illuminate\Database\Seeder;

class ProductDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Forkiva::seedDemoData()) {
            $this->call([
                ProductSeeder::class,
            ]);
        }
    }
}
