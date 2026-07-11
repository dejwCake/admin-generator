<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminUsersController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin-users')
    ->name('admin-users/')
    ->controller(AdminUsersController::class)
    ->group(static function (): void {
        Route::get('/', 'index')
            ->name('index');
        Route::get('/create', 'create')
            ->name('create');
        Route::post('/', 'store')
            ->name('store');
        Route::get('/{adminUser:id}/edit', 'edit')
            ->name('edit');
        Route::post('/{adminUser:id}', 'update')
            ->name('update');
        Route::delete('/{adminUser:id}', 'destroy')
            ->name('destroy');
        Route::get('/export', 'export')
            ->name('export');
        Route::get('/{adminUser:id}/impersonal-login', 'impersonalLogin')
            ->name('impersonal-login');
        Route::get('/{adminUser:id}/resend-activation', 'resendActivationEmail')
            ->name('resend-activation-email');
    });
