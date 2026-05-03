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
        Route::get('/{adminUser}/edit', 'edit')
            ->name('edit');
        Route::post('/bulk-destroy', 'bulkDestroy')
            ->name('bulk-destroy');
        Route::post('/{adminUser}', 'update')
            ->name('update');
        Route::delete('/{adminUser}', 'destroy')
            ->name('destroy');
        Route::get('/{adminUser}/impersonal-login', 'impersonalLogin')
            ->name('impersonal-login');
        Route::get('/{adminUser}/resend-activation', 'resendActivationEmail')
            ->name('resend-activation-email');
    });
