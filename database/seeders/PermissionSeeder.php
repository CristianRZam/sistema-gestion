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
        // DASHBOARD
        Permission::create(['name' => 'ver reporte general dashboard']);
        Permission::create(['name' => 'ver filtros dashboard']);

        Permission::create(['name' => 'ver ganancias de hoy dashboard']);
        Permission::create(['name' => 'ver compras de hoy dashboard']);
        Permission::create(['name' => 'ver capital real en stock dashboard']);
        Permission::create(['name' => 'ver valor venta stock dashboard']);

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
        Permission::create(['name' => 'ver permisos']);
        Permission::create(['name' => 'editar permiso']);

        // PARAMETROS
        Permission::create(['name' => 'ver parametros']);
        Permission::create(['name' => 'crear parametro']);
        Permission::create(['name' => 'editar parametro']);
        Permission::create(['name' => 'eliminar parametro']);
        Permission::create(['name' => 'exportar parametros']);

        // PRODUCTOS
        Permission::create(['name' => 'ver productos']);
        Permission::create(['name' => 'crear producto']);
        Permission::create(['name' => 'editar producto']);
        Permission::create(['name' => 'eliminar producto']);
        Permission::create(['name' => 'exportar productos']);
        Permission::create(['name' => 'importar productos']);
        Permission::create(['name' => 'descargar imagenes productos']);
        Permission::create(['name' => 'descargar catalogo productos']);

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

        // COMPRAS
        Permission::create(['name' => 'ver compras']);
        Permission::create(['name' => 'crear compra']);
        Permission::create(['name' => 'editar compra']);
        Permission::create(['name' => 'pagar compra']);
        Permission::create(['name' => 'ver compra']);
        Permission::create(['name' => 'eliminar compra']);
        Permission::create(['name' => 'exportar compras']);










        // HABITACIONES
        Permission::create(['name' => 'ver habitaciones']);
        Permission::create(['name' => 'crear habitacion']);
        Permission::create(['name' => 'editar habitacion']);
        Permission::create(['name' => 'exportar habitaciones']);

        // SERVICIOS
        Permission::create(['name' => 'ver servicios']);
        Permission::create(['name' => 'crear servicio']);
        Permission::create(['name' => 'editar servicio']);
        Permission::create(['name' => 'exportar servicios']);

        // RESERVAS
        Permission::create(['name' => 'ver reservas']);
        Permission::create(['name' => 'crear reserva']);
        Permission::create(['name' => 'editar reserva']);
        Permission::create(['name' => 'pagar reserva']);
        Permission::create(['name' => 'eliminar reserva']);
        Permission::create(['name' => 'exportar reservas']);
    }
}
