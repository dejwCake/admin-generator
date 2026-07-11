<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CategoriesController;
use Illuminate\Support\Facades\Route;

Route::prefix('billing-cats')
    ->name('billing-cats/')
    ->controller(CategoriesController::class)
    ->group(static function (): void {
        Route::get('/', 'index')
            ->name('index');
        Route::get('/create', 'create')
            ->name('create');
        Route::post('/', 'store')
            ->name('store');
        Route::get('/{cat:id}/edit', 'edit')
            ->name('edit');
        Route::post('/bulk-destroy', 'bulkDestroy')
            ->name('bulk-destroy');
        Route::post('/{cat:id}', 'update')
            ->name('update');
        Route::delete('/{cat:id}', 'destroy')
            ->name('destroy');
    });
