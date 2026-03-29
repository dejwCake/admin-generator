<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUsersController;


/* Auto-generated admin routes uses */

Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
    ->prefix('admin')
    ->name('admin/')
    ->group(static function (): void {
        /* Auto-generated admin-users routes */
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
                Route::post('/{adminUser}', 'update')
                    ->name('update');
                Route::delete('/{adminUser}', 'destroy')
                    ->name('destroy');
                Route::get('/{adminUser}/impersonal-login', 'impersonalLogin')
                    ->name('impersonal-login');
                Route::get('/{adminUser}/resend-activation', 'resendActivationEmail')
                    ->name('resend-activation-email');
            });
        /* End of admin-users routes */
        // Do not delete me :) I'm used for auto-generation of admin routes
    });
