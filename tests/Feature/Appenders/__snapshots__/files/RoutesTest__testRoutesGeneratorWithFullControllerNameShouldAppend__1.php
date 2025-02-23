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
                    [\App\Http\Billing\CategOryController::class, 'index'],
                )->name('index');
                Route::get(
                    '/create',
                    [\App\Http\Billing\CategOryController::class, 'create'],
                )->name('create');
                Route::post(
                    '/',
                    [\App\Http\Billing\CategOryController::class, 'store'],
                )->name('store');
                Route::get(
                    '/{category}/edit',
                    [\App\Http\Billing\CategOryController::class, 'edit'],
                )->name('edit');
                Route::post(
                    '/bulk-destroy',
                    [\App\Http\Billing\CategOryController::class, 'bulkDestroy'],
                )->name('bulk-destroy');
                Route::post(
                    '/{category}',
                    [\App\Http\Billing\CategOryController::class, 'update'],
                )->name('update');
                Route::delete(
                    '/{category}',
                    [\App\Http\Billing\CategOryController::class, 'destroy'],
                )->name('destroy');
            });
    });
