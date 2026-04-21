<?php

namespace Modules\Menu\Database\Seeders;

use App\Forkiva;
use Illuminate\Database\Seeder;
use Modules\Branch\Models\Branch;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\OnlineMenu;

class MenuDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Forkiva::seedDemoData()) {
            foreach (Branch::all() as $branch) {
                $menus = Menu::factory()->count(2)->create([
                    'branch_id' => $branch->id,
                    'is_active' => false,
                ]);
                $menus->random()->update(['is_active' => true]);
                OnlineMenu::factory()->create(['branch_id' => $branch->id]);
            }
        } else {
            Menu::query()->create([
                'branch_id' => Branch::main()->first()->id,
                'name' => "Main Menu",
                'is_active' => false,
            ]);
        }
    }
}
