<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Enums\DefaultRole;
use Modules\User\Enums\GenderType;
use Modules\User\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()
            ->updateOrCreate(
                ['email' => 'admin@forkiva.app', 'username' => "admin"],
                [
                    "name" => "Forkiva",
                    "password" => "12345678",
                    "gender" => GenderType::Male,
                    "is_active" => true
                ])
            ->assignRole(DefaultRole::SuperAdmin);

//        if (Forkiva::seedDemoData()) {
//            User::factory()
//                ->count(5)
//                ->withRandomRole([
//                    DefaultRole::SuperAdmin,
//                    DefaultRole::Admin,
//                ])
//                ->create();
//
//            User::factory()
//                ->count(30)
//                ->withBranch()
//                ->withRandomRole([
//                    DefaultRole::AdminBranch,
//                    DefaultRole::Manager,
//                    DefaultRole::Cashier,
//                    DefaultRole::Kitchen,
//                    DefaultRole::Waiter,
//                ])
//                ->create();
//        }
    }
}
