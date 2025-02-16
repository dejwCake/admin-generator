<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
    ->prefix('admin')
    ->name('admin/')
    ->group(static function (): void {
        Route::prefix('admin-users')
            ->name('admin-users/')
            ->group(static function (): void {
                Route::get(
                    '/',
                    [\App\Http\Controllers\Admin\AdminUsersController::class, 'index'],
                )->name('index');
                Route::get(
                    '/create',
                    [\App\Http\Controllers\Admin\AdminUsersController::class, 'create'],
                )->name('create');
                Route::post(
                    '/',
                    [\App\Http\Controllers\Admin\AdminUsersController::class, 'store'],
                )->name('store');
                Route::get(
                    '/{adminUser}/impersonal-login',
                    [\App\Http\Controllers\Admin\AdminUsersController::class, 'impersonalLogin'],
                )->name('impersonal-login');
                Route::get(
                    '/{adminUser}/edit',
                    [\App\Http\Controllers\Admin\AdminUsersController::class, 'edit'],
                )->name('edit');
                Route::post(
                    '/{adminUser}',
                    [\App\Http\Controllers\Admin\AdminUsersController::class, 'update'],
                )->name('update');
                Route::delete(
                    '/{adminUser}',
                    [\App\Http\Controllers\Admin\AdminUsersController::class, 'destroy'],
                )->name('destroy');
                Route::get(
                    '/export',
                    [\App\Http\Controllers\Admin\AdminUsersController::class, 'export'],
                )->name('export');
                Route::get(
                    '/{adminUser}/resend-activation',
                    [\App\Http\Controllers\Admin\AdminUsersController::class, 'resendActivationEmail'],
                )->name('resend-activation-email');
            });
    });