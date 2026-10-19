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
        Route::post('/bulk-destroy', 'bulkDestroy')
            ->name('bulk-destroy');
        Route::get('/{adminUser:id}/impersonal-login', 'impersonalLogin')
            ->name('impersonal-login');
        Route::get('/{adminUser:id}/resend-activation', 'resendActivationEmail')
            ->name('resend-activation-email');
    });
