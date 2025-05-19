<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Exports\CategoriesExcelExport;
use App\Exports\CategoriesPdfExport;
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

Route::middleware(['auth'])->group(function () {
    // Ruta para exportar Excel de categorías
    Route::get('/categories/exportar-excel', function () {
        return Excel::download(new CategoriesExcelExport, 'categorias.xlsx');
    })->name('categories.exportar.excel');


    Route::get('/categories/exportar-pdf', function () {
        return (new CategoriesPdfExport)->download('categorias.pdf');
    })->name('categories.exportar.pdf');


    // Rutas existentes
    Volt::route('categories', 'categories.lista')->name('categories');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
