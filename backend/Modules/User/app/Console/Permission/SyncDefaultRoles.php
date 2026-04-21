<?php

namespace Modules\User\Console\Permission;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\User\Enums\DefaultRole;
use Modules\User\Facades\Permission;
use Modules\User\Models\Role;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Console\Input\InputOption;

class SyncDefaultRoles extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $name = 'permission:sync-default-roles';

    /**
     * The console command description.
     */
    protected $description = 'Sync default roles with database.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        if ($this->option('force')) {
            $this->forceCreateOrUpdate();
        } else {
            $this->createMissingRoles();
        }

        $this->syncPermissions();
    }

    /**
     * Force sync default roles, even if they already exist
     *
     * @return void
     */
    private function forceCreateOrUpdate(): void
    {
        $hrModuleEnable = Module::find('hr');

        foreach (DefaultRole::cases() as $case) {
            if (in_array($case->name, [DefaultRole::HrOfficer->value, DefaultRole::HrManager->value]) && !$hrModuleEnable) {
                continue;
            }
            Role::updateOrCreate(
                attributes: [
                    "name" => $case->value
                ],
                values: [
                    "built_in" => true,
                    'display_name' => $case->allTrans()
                ]
            );
        }
    }

    /**
     * Create only the missing default rules
     *
     * @return void
     */
    private function createMissingRoles(): void
    {
        $roles = array_diff(
            DefaultRole::values(),
            DB::table((new Role)->getTable())->pluck('name')->toArray()
        );

        $hrModuleEnable = Module::find('hr');

        foreach ($roles as $role) {
            if (in_array($role, [DefaultRole::HrOfficer->value, DefaultRole::HrManager->value]) && !$hrModuleEnable) {
                continue;
            }
            Role::create([
                "name" => $role,
                "built_in" => true,
                'display_name' => DefaultRole::tryFrom($role)->allTrans()
            ]);
        }
    }

    /**
     * Roles sync permissions
     *
     * @return void
     */
    public function syncPermissions(): void
    {
        $allPermission = Permission::getPermissionNames();

        $roles = Role::whereIn('name', DefaultRole::values())
            ->whereNot("name", DefaultRole::SuperAdmin->value)
            ->get();

        $hrModuleEnable = Module::find('hr');

        /** @var Role $role */
        foreach ($roles as $role) {
            if (in_array($role->name, [DefaultRole::HrOfficer->value, DefaultRole::HrManager->value]) && is_null($hrModuleEnable)) {
                continue;
            }
            $rolePermission = DefaultRole::from($role->name)->getPermissions();
            $role->syncPermissions($rolePermission === '*' ? $allPermission : $rolePermission);
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Force sync default roles, even if they already exist.'],
        ];
    }
}
