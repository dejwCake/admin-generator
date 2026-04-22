<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoriesController;


//-- Do not delete me :) I'm used for auto-generation admin routes uses --

Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
    ->prefix('admin')
    ->name('admin/')
    ->group(static function (): void {
        /* Auto-generated categories routes */
        Route::prefix('categories')
            ->name('categories/')
            ->controller(CategoriesController::class)
            ->group(static function (): void {
                Route::get('/', 'index')
                    ->name('index');
                Route::get('/create', 'create')
                    ->name('create');
                Route::post('/', 'store')
                    ->name('store');
                Route::get('/{category}/edit', 'edit')
                    ->name('edit');
                Route::post('/bulk-destroy', 'bulkDestroy')
                    ->name('bulk-destroy');
                Route::post('/{category}', 'update')
                    ->name('update');
                Route::delete('/{category}', 'destroy')
                    ->name('destroy');
                Route::get('/{category}/impersonal-login', 'impersonalLogin')
                    ->name('impersonal-login');
                Route::get('/{category}/resend-activation', 'resendActivationEmail')
                    ->name('resend-activation-email');
            });
        /* End of categories routes */
        //-- Do not delete me :) I'm used for auto-generation admin routes --
    });
