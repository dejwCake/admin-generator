<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CategoriesController;
use App\Http\Controllers\Admin\CategoriesTrashController;
use Illuminate\Support\Facades\Route;

Route::prefix('categories')
    ->name('categories/')
    ->controller(CategoriesTrashController::class)
    ->group(static function (): void {
        Route::post('/bulk-force-destroy', 'bulkForceDestroy')
            ->name('bulk-force-destroy');
        Route::post('/bulk-restore', 'bulkRestore')
            ->name('bulk-restore');
        Route::delete('/{category:id}/force', 'forceDestroy')
            ->name('force-destroy')
            ->withTrashed();
        Route::post('/{category:id}/restore', 'restore')
            ->name('restore')
            ->withTrashed();
    });

Route::prefix('categories')
    ->name('categories/')
    ->controller(CategoriesController::class)
    ->group(static function (): void {
        Route::get('/', 'index')
            ->name('index');
        Route::get('/{category:id}/edit', 'edit')
            ->name('edit');
        Route::post('/bulk-destroy', 'bulkDestroy')
            ->name('bulk-destroy');
        Route::post('/{category:id}', 'update')
            ->name('update');
        Route::delete('/{category:id}', 'destroy')
            ->name('destroy');
        Route::get('/export', 'export')
            ->name('export');
        Route::get('/{category:id}', 'show')
            ->name('show');
    });
