<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoriesController;


/* Auto-generated admin routes uses */

Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
    ->prefix('admin')
    ->name('admin/')
    ->group(static function (): void {
        /* Auto-generated billing-categ-ories routes */
        Route::prefix('billing-categ-ories')
            ->name('billing-categ-ories/')
            ->controller(CategoriesController::class)
            ->group(static function (): void {
                Route::get('/', 'index')
                    ->name('index');
                Route::get('/create', 'create')
                    ->name('create');
                Route::post('/', 'store')
                    ->name('store');
                Route::get('/{categOry}/edit', 'edit')
                    ->name('edit');
                Route::post('/bulk-destroy', 'bulkDestroy')
                    ->name('bulk-destroy');
                Route::post('/{categOry}', 'update')
                    ->name('update');
                Route::delete('/{categOry}', 'destroy')
                    ->name('destroy');
            });
        /* End of billing-categ-ories routes */
        // Do not delete me :) I'm used for auto-generation of admin routes
    });
