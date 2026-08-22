<?php

namespace Modules\Report\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ReportPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allPermissionArray = config('Modules.Report.acl.permissions', []);


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
