<?php

namespace Modules\SeatingPlan\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\SeatingPlan\Models\Floor;
use Modules\SeatingPlan\Models\Zone;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zoneNames = [
            ['en' => 'Indoor', 'ar' => 'الداخلية'],
            ['en' => 'Outdoor', 'ar' => 'الخارجية'],
            ['en' => 'VIP', 'ar' => 'كبار الشخصيات'],
            ['en' => 'Terrace', 'ar' => 'الشرفة'],
            ['en' => 'Smoking Area', 'ar' => 'منطقة التدخين'],
            ['en' => 'Family Area', 'ar' => 'منطقة العائلات'],
            ['en' => 'Quiet Zone', 'ar' => 'منطقة الهدوء'],
            ['en' => 'Kids Zone', 'ar' => 'منطقة الأطفال'],
        ];

        $floors = Floor::with('branch')->get();

        foreach ($floors as $floor) {
            $count = rand(2, 4);
            $usedNames = collect($zoneNames)->shuffle()->take($count);

            foreach ($usedNames as $name) {
                Zone::factory()
                    ->create([
                        'floor_id' => $floor->id,
                        'branch_id' => $floor->branch_id,
                        'name' => $name,
                    ]);
            }
        }
    }
}
