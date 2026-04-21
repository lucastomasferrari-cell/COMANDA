<?php

namespace Modules\Inventory\Database\Seeders;

use App\Forkiva;
use Illuminate\Database\Seeder;

class InventoryDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            UnitSeeder::class,
            ...(Forkiva::seedDemoData()
                ? [
                    SupplierSeeder::class,
                    IngredientSeeder::class,
                    PurchaseSeeder::class,
                    StockMovementSeeder::class,
                ] :
                [])
        ]);
    }
}
