<?php

namespace Modules\SeatingPlan\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\SeatingPlan\Models\Table;
use Modules\SeatingPlan\Models\Zone;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = Zone::all();

        foreach ($zones as $zone) {
            $tableCount = rand(3, 6);

            for ($i = 1; $i <= $tableCount; $i++) {
                $number = str_pad($i, 2, '0', STR_PAD_LEFT);

                Table::factory()->create([
                    'floor_id' => $zone->floor_id,
                    'zone_id' => $zone->id,
                    'branch_id' => $zone->branch_id,
                    'name' => [
                        'en' => "T$number",
                        'ar' => "T$number",
                    ],
                ]);
            }
        }
    }
}
