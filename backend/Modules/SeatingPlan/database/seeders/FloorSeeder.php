<?php

namespace Modules\SeatingPlan\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Branch\Models\Branch;
use Modules\SeatingPlan\Models\Floor;

class FloorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = Branch::all();

        foreach ($branches as $branch) {
            $floorCount = rand(1, 2);

            for ($i = 1; $i <= $floorCount; $i++) {
                Floor::factory()
                    ->create([
                        'branch_id' => $branch->id,
                        'name' => ['en' => "Floor $i", 'ar' => "الطابق $i",]
                    ]);
            }
        }
    }
}
