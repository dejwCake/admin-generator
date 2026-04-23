<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUsersController;


//-- Do not delete me :) I'm used for auto-generation admin routes uses --

Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
    ->prefix('admin')
    ->name('admin/')
    ->group(static function (): void {
        /* Auto-generated auth-users routes */
        Route::prefix('auth-users')
            ->name('auth-users/')
            ->controller(AdminUsersController::class)
            ->group(static function (): void {
                Route::get('/', 'index')
                    ->name('index');
                Route::get('/create', 'create')
                    ->name('create');
                Route::post('/', 'store')
                    ->name('store');
                Route::get('/{user}/edit', 'edit')
                    ->name('edit');
                Route::post('/bulk-destroy', 'bulkDestroy')
                    ->name('bulk-destroy');
                Route::post('/{user}', 'update')
                    ->name('update');
                Route::delete('/{user}', 'destroy')
                    ->name('destroy');
                Route::get('/{user}/impersonal-login', 'impersonalLogin')
                    ->name('impersonal-login');
                Route::get('/{user}/resend-activation', 'resendActivationEmail')
                    ->name('resend-activation-email');
            });
        /* End of auth-users routes */
        //-- Do not delete me :) I'm used for auto-generation admin routes --
    });
