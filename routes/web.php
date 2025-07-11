<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Exports\RolesExcelExport;
use App\Exports\RolesPdfExport;
use App\Exports\UsersExcelExport;
use App\Exports\UsersPdfExport;
use App\Exports\ProductsExcelExport;
use App\Exports\ProductsPdfExport;
use App\Exports\ProductsCatalogPdfExport;
use \App\Http\Controllers\ProductController;
use App\Exports\CustomersExcelExport;
use App\Exports\CustomersPdfExport;
use App\Exports\SuppliersExcelExport;
use App\Exports\SuppliersPdfExport;
use App\Exports\SalesExcelExport;
use App\Exports\SalesPdfExport;
use App\Livewire\Sales\Pay;
use App\Exports\ParametersExcelExport;
use App\Exports\ParametersPdfExport;
use App\Http\Controllers\ParametroController;
use App\Exports\PurchasesExcelExport;
use App\Exports\PurchasesPdfExport;
use App\Exports\RoomsExcelExport;
use App\Exports\RoomsPdfExport;
use App\Exports\ServicesExcelExport;
use App\Exports\ServicesPdfExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Http\Middleware\EnsureUserIsActive;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', EnsureUserIsActive::class])
    ->name('dashboard');

Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});


Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    // Ruta para exportar Excel de usuarios
    Route::get('/users/exportar-excel', function (Request $request) {
        return Excel::download(new UsersExcelExport($request), 'users.xlsx');
    })->middleware('can:exportar usuarios')->name('users.exportar.excel');

    // Ruta para exportar PDF de usuarios
    Route::get('/users/exportar-pdf', function (Request $request) {
        return (new UsersPdfExport($request))->download('users.pdf');
    })->middleware('can:exportar usuarios')->name('users.exportar.pdf');

    // Rutas existentes
    Volt::route('users', 'users.lista')->middleware('can:ver usuarios')->name('users');
});

Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    // Ruta para exportar Excel de roles
    Route::get('/roles/exportar-excel', function () {
        return Excel::download(new RolesExcelExport(), 'roles.xlsx');
    })->middleware('can:exportar roles')->name('roles.exportar.excel');

    // Ruta para exportar PDF de roles
    Route::get('/roles/exportar-pdf', function () {
        return (new RolesPdfExport)->download('roles.pdf');
    })->middleware('can:exportar roles')->name('roles.exportar.pdf');

    // Rutas existentes
    Volt::route('roles', 'roles.lista')->middleware('can:ver roles')->name('roles');
});



Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    Volt::route('/roles/{role}/permissions', 'permissions.lista')->middleware('can:ver permisos')->name('permissions');
});


Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    // Ruta para exportar Excel de productos
    Route::get('/products/exportar-excel', function (Request $request) {
        return Excel::download(new ProductsExcelExport($request), 'productos.xlsx');
    })->middleware('can:exportar productos')->name('products.exportar.excel');

    // Ruta para exportar PDF de categorías
    Route::get('/products/exportar-pdf', function (Request $request) {
        return (new ProductsPdfExport($request))->download('productos.pdf');
    })->middleware('can:exportar productos')->name('products.exportar.pdf');

    Route::get('/products/exportar-catalogo-pdf', function (Request $request) {
        return (new ProductsCatalogPdfExport($request))->download('catalogo-productos.pdf');
    })->middleware('can:descargar catalogo productos')->name('products.exportar-catalogo.pdf');

    // Rutas existentes
    Volt::route('products', 'products.lista')->middleware('can:ver productos')->name('products');
});

Route::get('/products/descargar-imagenes', [ProductController::class, 'descargarImagenes'])
    ->name('products.descargar.imagenes')
    ->middleware(['auth', 'can:descargar imagenes productos', EnsureUserIsActive::class]);


Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    // Exportar Excel de customers
    Route::get('/customers/exportar-excel', function (Request $request) {
        return Excel::download(new CustomersExcelExport($request), 'clientes.xlsx');
    })->middleware('can:exportar clientes')->name('customers.exportar.excel');

    // Exportar PDF de customers
    Route::get('/customers/exportar-pdf', function (Request $request) {
        return (new CustomersPdfExport($request))->download('clientes.pdf');
    })->middleware('can:exportar clientes')->name('customers.exportar.pdf');

    // Ruta lista customers (si la tienes)
    Volt::route('customers', 'customers.lista')->middleware('can:ver clientes')->name('customers');
});

Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    // Exportar Excel de suppliers
    Route::get('/suppliers/exportar-excel', function (Request $request) {
        return Excel::download(new SuppliersExcelExport($request), 'proveedores.xlsx');
    })->middleware('can:exportar proveedores')->name('suppliers.exportar.excel');

    // Exportar PDF de suppliers
    Route::get('/suppliers/exportar-pdf', function (Request $request) {
        return (new SuppliersPdfExport($request))->download('proveedores.pdf');
    })->middleware('can:exportar proveedores')->name('suppliers.exportar.pdf');

    // Ruta lista suppliers (si la tienes)
    Volt::route('suppliers', 'suppliers.lista')->middleware('can:ver proveedores')->name('suppliers');
});

Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    // Exportar Excel de ventas
    Route::get('/sales/exportar-excel', function (Request $request) {
        return Excel::download(new SalesExcelExport($request), 'ventas.xlsx');
    })->middleware('can:exportar ventas')->name('sales.exportar.excel');

    // Exportar PDF de ventas
    Route::get('/sales/exportar-pdf', function (Request $request) {
        return (new SalesPdfExport($request))->download('ventas.pdf');
    })->middleware('can:exportar ventas')->name('sales.exportar.pdf');

    // Lista de ventas
    Volt::route('sales', 'sales.lista')
        ->middleware('can:ver ventas')
        ->name('sales');

    // Crear venta
    Volt::route('sales/add', 'sales.register')
        ->middleware('can:crear venta')
        ->name('sales.register');

    // Editar venta
    Volt::route('sales/edit/{id}', 'sales.register')
        ->middleware('can:editar venta')
        ->name('sales.edit');

    // Registrar pago
    Volt::route('sales/pay/{venta}', 'sales.pay')
        ->middleware('can:pagar venta') // O crea 'pagar venta' si quieres más granularidad
        ->name('sales.pay');
});

Route::get('/comprobante/preview/{ventaId}', [Pay::class, 'vistaComprobantePreview'])->name('comprobante.preview');

Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    // Exportar Excel de ventas
    Route::get('/purchases/exportar-excel', function (Request $request) {
        return Excel::download(new PurchasesExcelExport($request), 'compras.xlsx');
    })->middleware('can:exportar compras')->name('purchases.exportar.excel');

    // Exportar PDF de ventas
    Route::get('/purchases/exportar-pdf', function (Request $request) {
        return (new PurchasesPdfExport($request))->download('compras.pdf');
    })->middleware('can:exportar compras')->name('purchases.exportar.pdf');

    // Lista de ventas
    Volt::route('purchases', 'purchases.lista')
        ->middleware('can:ver compras')
        ->name('purchases');

    // Crear venta
    Volt::route('purchases/add', 'purchases.register')
        ->middleware('can:crear compra')
        ->name('purchases.register');

    // Editar venta
    Volt::route('purchases/edit/{id}', 'purchases.register')
        ->middleware('can:editar compra')
        ->name('purchases.edit');

    // Registrar pago
    Volt::route('purchases/pay/{compra}', 'purchases.pay')
        ->middleware('can:pagar compra') // O crea 'pagar venta' si quieres más granularidad
        ->name('purchases.pay');
});

Route::get('/comprobante/compra/preview/{compraId}', [\App\Livewire\Purchases\Pay::class, 'vistaComprobantePreview'])->name('comprobante.compra.preview');

Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    Route::get('/parameters/exportar-excel', function (Request $request) {
        return Excel::download(new ParametersExcelExport($request), 'parametros.xlsx');
    })->middleware('can:exportar parametros')->name('parameters.exportar.excel');

    // Ruta para exportar PDF de categorías
    Route::get('/parameters/exportar-pdf', function (Request $request) {
        return (new ParametersPdfExport($request))->download('parametros.pdf');
    })->middleware('can:exportar parametros')->name('parameters.exportar.pdf');

    // Rutas ver parametros (configuraciones)
    Volt::route('parameters', 'parameters.lista')->middleware('can:ver parametros')->name('parameters');
});

Route::get('/parameters/dowloader/{codigoParametro}', [ParametroController::class, 'descargar'])->middleware(['auth', EnsureUserIsActive::class])->name('parametros.descargar');





Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    // Ruta para exportar Excel de usuarios
    Route::get('/rooms/exportar-excel', function (Request $request) {
        return Excel::download(new RoomsExcelExport($request), 'rooms.xlsx');
    })->middleware('can:exportar habitaciones')->name('rooms.exportar.excel');

    // Ruta para exportar PDF de usuarios
    Route::get('/rooms/exportar-pdf', function (Request $request) {
        return (new RoomsPdfExport($request))->download('rooms.pdf');
    })->middleware('can:exportar habitaciones')->name('rooms.exportar.pdf');

    // Rutas existentes
    Volt::route('rooms', 'rooms.lista')->middleware('can:ver habitaciones')->name('rooms');
});

Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    // Ruta para exportar Excel de usuarios
    Route::get('/services/exportar-excel', function () {
        return Excel::download(new ServicesExcelExport, 'services.xlsx');
    })->middleware('can:exportar servicios')->name('services.exportar.excel');

    // Ruta para exportar PDF de usuarios
    Route::get('/services/exportar-pdf', function () {
        return (new ServicesPdfExport)->download('services.pdf');
    })->middleware('can:exportar servicios')->name('services.exportar.pdf');

    // Rutas existentes
    Volt::route('services', 'services.lista')->middleware('can:ver servicios')->name('services');
});

Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    // Exportar Excel de ventas
    Route::get('/reservations/exportar-excel', function (Request $request) {
        return Excel::download(new SalesExcelExport($request), 'reservaciones.xlsx');
    })->middleware('can:exportar ventas')->name('reservations.exportar.excel');

    // Exportar PDF de ventas
    Route::get('/reservations/exportar-pdf', function (Request $request) {
        return (new SalesPdfExport($request))->download('reservaciones.pdf');
    })->middleware('can:exportar ventas')->name('reservations.exportar.pdf');

    // Lista de ventas
    Volt::route('reservations', 'reservations.lista')
        ->middleware('can:ver reservas')
        ->name('reservations');

    // selector de habitacines
    Volt::route('reservations/selector', 'reservations.room-selector')
        ->middleware('can:crear reserva')
        ->name('reservations.selector');

    // Crear venta
    Volt::route('reservations/add', 'sales.register')
        ->middleware('can:crear venta')
        ->name('reservations.register');

    // Editar venta
    Volt::route('reservations/edit/{id}', 'sales.register')
        ->middleware('can:editar venta')
        ->name('reservations.edit');

    // Registrar pago
    Volt::route('reservations/pay/{venta}', 'sales.pay')
        ->middleware('can:pagar venta') // O crea 'pagar venta' si quieres más granularidad
        ->name('reservations.pay');
});

require __DIR__.'/auth.php';
