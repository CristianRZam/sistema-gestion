<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleSuperAdministrador = Role::create(['name' => 'super administrador']);

        $permissionsSuperAdministrador = Permission::query()->pluck('name');
        $roleSuperAdministrador->syncPermissions($permissionsSuperAdministrador);

        $roleAdministrador = Role::create(['name' => 'administrador']);
        $roleAdministrador->SyncPermissions('exportar categorias');
    }
}
