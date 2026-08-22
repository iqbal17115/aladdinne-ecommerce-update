<?php

namespace Modules\PreOrder\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PreOrderPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allPermissionArray = config('Modules.PreOrder.acl.permissions', []);
        foreach ($allPermissionArray as $modelType => $allPermissions) {
            foreach ($allPermissions as $permissionName => $permissionValues) {
                foreach ($permissionValues as $permission) {
                   Permission::firstOrCreate([
                        'name' => $modelType.'.'.$permissionName.'.'.$permission,
                        'guard_name' => 'web',
                    ]);
                }
            }
        }
    }
}
