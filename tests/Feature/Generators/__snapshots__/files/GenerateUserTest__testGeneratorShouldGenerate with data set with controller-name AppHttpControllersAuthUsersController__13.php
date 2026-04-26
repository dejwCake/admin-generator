<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UsersController;


//-- Do not delete me :) I'm used for auto-generation admin routes uses --

Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
    ->prefix('admin')
    ->name('admin/')
    ->group(static function (): void {
        /* Auto-generated users routes */
        Route::prefix('users')
            ->name('users/')
            ->controller(UsersController::class)
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
                Route::get('/{user}/resend-verify-email', 'resendVerifyEmail')
                    ->name('resend-verify-email');
                Route::get('/{user}/impersonal-login', 'impersonalLogin')
                    ->name('impersonal-login');
            });
        /* End of users routes */
        //-- Do not delete me :) I'm used for auto-generation admin routes --
    });
