<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
    ->prefix('admin')
    ->name('admin/')
    ->group(static function (): void {
        Route::prefix('categories')
            ->name('categories/')
            ->group(static function (): void {
                Route::get(
                    '/',
                    [\App\Http\Controllers\Admin\CategoriesController::class, 'index'],
                )->name('index');
                Route::get(
                    '/create',
                    [\App\Http\Controllers\Admin\CategoriesController::class, 'create'],
                )->name('create');
                Route::post(
                    '/',
                    [\App\Http\Controllers\Admin\CategoriesController::class, 'store'],
                )->name('store');
                Route::get(
                    '/{category}/edit',
                    [\App\Http\Controllers\Admin\CategoriesController::class, 'edit'],
                )->name('edit');
                Route::post(
                    '/bulk-destroy',
                    [\App\Http\Controllers\Admin\CategoriesController::class, 'bulkDestroy'],
                )->name('bulk-destroy');
                Route::post(
                    '/{category}',
                    [\App\Http\Controllers\Admin\CategoriesController::class, 'update'],
                )->name('update');
                Route::delete(
                    '/{category}',
                    [\App\Http\Controllers\Admin\CategoriesController::class, 'destroy'],
                )->name('destroy');
                Route::get(
                    '/export',
                    [\App\Http\Controllers\Admin\CategoriesController::class, 'export'],
                )->name('export');
            });
    });
