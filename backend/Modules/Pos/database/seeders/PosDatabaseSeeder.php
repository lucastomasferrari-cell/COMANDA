<?php

namespace Modules\Pos\Database\Seeders;

use App\Forkiva;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Modules\Branch\Models\Branch;
use Modules\Pos\Enums\PosSessionStatus;
use Modules\Pos\Models\PosRegister;
use Modules\Pos\Models\PosSession;

class PosDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Forkiva::seedDemoData()) {
            foreach (Branch::all() as $branch) {
                $registers = PosRegister::factory()->count(rand(3, 5))->for($branch)->create();
                $this->posSessionSeeder($branch, $registers);
            }
            $this->call([
                PosCashMovementSeeder::class,
            ]);
        }
    }

    /**
     * Pos session seeder
     *
     * @param Branch $branch
     * @param Collection $registers
     * @return void
     */
    private function posSessionSeeder(Branch $branch, Collection $registers): void
    {
        foreach ($registers as $register) {
            PosSession::factory()
                ->count(rand(1, 3))
                ->forBranch($branch->id, $register->id, PosSessionStatus::Closed)
                ->create();

            PosSession::factory()
                ->forBranch($branch->id, $register->id, PosSessionStatus::Open)
                ->create();
        }
    }
}
