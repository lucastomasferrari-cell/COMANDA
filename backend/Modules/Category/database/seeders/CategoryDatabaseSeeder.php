<?php

namespace Modules\Category\Database\Seeders;

use App\Forkiva;
use Illuminate\Database\Seeder;

class CategoryDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Forkiva::seedDemoData()) {
            $this->call([
                CategorySeeder::class,
            ]);
        }
    }
}
