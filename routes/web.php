<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Exports\CategoriesExcelExport;
use App\Exports\CategoriesPdfExport;
use App\Exports\RolesExcelExport;
use App\Exports\RolesPdfExport;
use App\Exports\UsersExcelExport;
use App\Exports\UsersPdfExport;
use App\Exports\ProductsExcelExport;
use App\Exports\ProductsPdfExport;
use App\Exports\CustomersExcelExport;
use App\Exports\CustomersPdfExport;
use App\Exports\SuppliersExcelExport;
use App\Exports\SuppliersPdfExport;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::middleware(['auth', 'can:ver categorias'])->group(function () {
    // Ruta para exportar Excel de categorías
    Route::get('/categories/exportar-excel', function () {
        return Excel::download(new CategoriesExcelExport, 'categorias.xlsx');
    })->name('categories.exportar.excel');

    // Ruta para exportar PDF de categorías
    Route::get('/categories/exportar-pdf', function () {
        return (new CategoriesPdfExport)->download('categorias.pdf');
    })->name('categories.exportar.pdf');


    // Rutas existentes
    Volt::route('categories', 'categories.lista')->name('categories');
    //Volt::route('settings/password', 'settings.password')->name('settings.password');
    //Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::middleware(['auth', 'can:ver usuarios'])->group(function () {
    // Ruta para exportar Excel de usuarios
    Route::get('/users/exportar-excel', function () {
        return Excel::download(new UsersExcelExport, 'users.xlsx');
    })->name('users.exportar.excel');

    // Ruta para exportar PDF de usuarios
    Route::get('/users/exportar-pdf', function () {
        return (new UsersPdfExport)->download('users.pdf');
    })->name('users.exportar.pdf');

    // Rutas existentes
    Volt::route('users', 'users.lista')->name('users');
    //Volt::route('settings/password', 'settings.password')->name('settings.password');
    //Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::middleware(['auth', 'can:ver roles'])->group(function () {
    // Ruta para exportar Excel de roles
    Route::get('/roles/exportar-excel', function () {
        return Excel::download(new RolesExcelExport, 'roles.xlsx');
    })->name('roles.exportar.excel');

    // Ruta para exportar PDF de roles
    Route::get('/roles/exportar-pdf', function () {
        return (new RolesPdfExport)->download('roles.pdf');
    })->name('roles.exportar.pdf');

    // Rutas existentes
    Volt::route('roles', 'roles.lista')->name('roles');
    //Volt::route('settings/password', 'settings.password')->name('settings.password');
    //Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});


Route::middleware(['auth'])->group(function () {
    Volt::route('/roles/{role}/permissions', 'permissions.lista')->name('permissions');
});


Route::middleware(['auth', 'can:ver productos'])->group(function () {
    // Ruta para exportar Excel de productos
    Route::get('/products/exportar-excel', function () {
        return Excel::download(new ProductsExcelExport, 'productos.xlsx');
    })->name('products.exportar.excel');

    // Ruta para exportar PDF de categorías
   Route::get('/products/exportar-pdf', function () {
        return (new ProductsPdfExport)->download('productos.pdf');
    })->name('products.exportar.pdf');


    // Rutas existentes
    Volt::route('products', 'products.lista')->name('products');
    //Volt::route('settings/password', 'settings.password')->name('settings.password');
    //Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::middleware(['auth', 'can:ver clientes'])->group(function () {
    // Exportar Excel de customers
    Route::get('/customers/exportar-excel', function () {
        return Excel::download(new CustomersExcelExport, 'clientes.xlsx');
    })->name('customers.exportar.excel');

    // Exportar PDF de customers
    Route::get('/customers/exportar-pdf', function () {
        return (new CustomersPdfExport)->download('clientes.pdf');
    })->name('customers.exportar.pdf');

    // Ruta lista customers (si la tienes)
    Volt::route('customers', 'customers.lista')->name('customers');
});

Route::middleware(['auth', 'can:ver proveedores'])->group(function () {
    // Exportar Excel de suppliers
    Route::get('/suppliers/exportar-excel', function () {
        return Excel::download(new SuppliersExcelExport, 'proveedores.xlsx');
    })->name('suppliers.exportar.excel');

    // Exportar PDF de suppliers
    Route::get('/suppliers/exportar-pdf', function () {
        return (new SuppliersPdfExport)->download('proveedores.pdf');
    })->name('suppliers.exportar.pdf');

    // Ruta lista suppliers (si la tienes)
    Volt::route('suppliers', 'suppliers.lista')->name('suppliers');
});


require __DIR__.'/auth.php';
