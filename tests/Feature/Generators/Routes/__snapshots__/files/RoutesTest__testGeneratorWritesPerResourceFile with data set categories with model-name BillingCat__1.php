<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CategoriesController;
use App\Http\Controllers\Admin\CategoriesTrashController;
use Illuminate\Support\Facades\Route;

Route::prefix('billing-cats')
    ->name('billing-cats/')
    ->controller(CategoriesTrashController::class)
    ->group(static function (): void {
        Route::post('/bulk-force-destroy', 'bulkForceDestroy')
            ->name('bulk-force-destroy');
        Route::post('/bulk-restore', 'bulkRestore')
            ->name('bulk-restore');
        Route::delete('/{cat:id}/force', 'forceDestroy')
            ->name('force-destroy')
            ->withTrashed();
        Route::post('/{cat:id}/restore', 'restore')
            ->name('restore')
            ->withTrashed();
    });

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
        Route::get('/{cat:id}', 'show')
            ->name('show');
    });
