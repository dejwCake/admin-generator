<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
    ->prefix('admin')
    ->name('admin/')
    ->group(static function (): void {
        Route::prefix('users')
            ->name('users/')
            ->group(static function(): void {
                Route::get('/', [\App\Http\Controllers\Admin\Auth\UsersController::class, 'index'])
                    ->name('index');
                Route::get('/create', [\App\Http\Controllers\Admin\Auth\UsersController::class, 'create'])
                    ->name('create');
                Route::post('/', [\App\Http\Controllers\Admin\Auth\UsersController::class, 'store'])
                    ->name('store');
                Route::get('/user/impersonal-login', [\App\Http\Controllers\Admin\Auth\UsersController::class, 'impersonalLogin'])
                    ->name('impersonal-login');
                Route::get('/user/edit', [\App\Http\Controllers\Admin\Auth\UsersController::class, 'edit'])
                    ->name('edit');
                Route::post('/user', [\App\Http\Controllers\Admin\Auth\UsersController::class, 'update'])
                    ->name('update');
                Route::delete('/user', [\App\Http\Controllers\Admin\Auth\UsersController::class, 'destroy'])
                    ->name('destroy');
                Route::get('/user/resend-activation', [\App\Http\Controllers\Admin\Auth\UsersController::class, 'resendActivationEmail'])
                    ->name('resend-activation-email');
            });
    });