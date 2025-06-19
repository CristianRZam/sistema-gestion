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

        // PARAMETROS
        Permission::create(['name' => 'ver parametros']);
        Permission::create(['name' => 'crear parametro']);
        Permission::create(['name' => 'editar parametro']);
        Permission::create(['name' => 'eliminar parametro']);
        Permission::create(['name' => 'exportar parametros']);

        // CATEGORIAS
        Permission::create(['name' => 'ver productos']);
        Permission::create(['name' => 'crear producto']);
        Permission::create(['name' => 'editar producto']);
        Permission::create(['name' => 'eliminar producto']);
        Permission::create(['name' => 'exportar productos']);
        Permission::create(['name' => 'importar productos']);

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

        // VENTAS
        Permission::create(['name' => 'ver ventas']);
        Permission::create(['name' => 'crear venta']);
        Permission::create(['name' => 'editar venta']);
        Permission::create(['name' => 'pagar venta']);
        Permission::create(['name' => 'ver venta']);
        Permission::create(['name' => 'eliminar venta']);
        Permission::create(['name' => 'exportar ventas']);
    }
}
