<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'ver categorias']);
        Permission::create(['name' => 'crear categoria']);
        Permission::create(['name' => 'editar categoria']);
        Permission::create(['name' => 'eliminar categoria']);
        Permission::create(['name' => 'exportar categorias']);
    }
}
