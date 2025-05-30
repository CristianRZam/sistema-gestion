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
        // USUARIOS
        Permission::create(['name' => 'ver usuarios']);
        Permission::create(['name' => 'editar usuario']);
        Permission::create(['name' => 'exportar usuarios']);

        // ROLES
        Permission::create(['name' => 'ver roles']);
        Permission::create(['name' => 'crear rol']);
        Permission::create(['name' => 'editar rol']);
        Permission::create(['name' => 'exportar roles']);

        // PERMISOS
        Permission::create(['name' => 'editar permisos']);

        // CATEGORIAS
        Permission::create(['name' => 'ver categorias']);
        Permission::create(['name' => 'crear categoria']);
        Permission::create(['name' => 'editar categoria']);
        Permission::create(['name' => 'eliminar categoria']);
        Permission::create(['name' => 'exportar categorias']);

        // CATEGORIAS
        Permission::create(['name' => 'ver productos']);
        Permission::create(['name' => 'crear producto']);
        Permission::create(['name' => 'editar producto']);
        Permission::create(['name' => 'eliminar producto']);
        Permission::create(['name' => 'exportar productos']);

        // CLIENTES
        Permission::create(['name' => 'ver clientes']);
        Permission::create(['name' => 'crear cliente']);
        Permission::create(['name' => 'editar cliente']);
        Permission::create(['name' => 'eliminar cliente']);
        Permission::create(['name' => 'exportar clientes']);

        // PROVEEDORES
        Permission::create(['name' => 'ver proveedores']);
        Permission::create(['name' => 'crear proveedor']);
        Permission::create(['name' => 'editar proveedor']);
        Permission::create(['name' => 'eliminar proveedor']);
        Permission::create(['name' => 'exportar proveedores']);
    }
}
