<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CategoriesController;
use Illuminate\Support\Facades\Route;

Route::prefix('custom-resource')
    ->name('custom-resource/')
    ->controller(CategoriesController::class)
    ->group(static function (): void {
        Route::get('/', 'index')
            ->name('index');
        Route::get('/create', 'create')
            ->name('create');
        Route::post('/', 'store')
            ->name('store');
        Route::get('/{category}/edit', 'edit')
            ->name('edit');
        Route::post('/bulk-destroy', 'bulkDestroy')
            ->name('bulk-destroy');
        Route::post('/{category}', 'update')
            ->name('update');
        Route::delete('/{category}', 'destroy')
            ->name('destroy');
    });
