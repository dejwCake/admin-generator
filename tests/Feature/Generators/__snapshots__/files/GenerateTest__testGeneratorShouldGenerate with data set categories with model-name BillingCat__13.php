<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoriesController;


//-- Do not delete me :) I'm used for auto-generation admin routes uses --

Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
    ->prefix('admin')
    ->name('admin/')
    ->group(static function (): void {
        /* Auto-generated billing-cats routes */
        Route::prefix('billing-cats')
            ->name('billing-cats/')
            ->controller(CategoriesController::class)
            ->group(static function (): void {
                Route::get('/', 'index')
                    ->name('index');
                Route::get('/create', 'create')
                    ->name('create');
                Route::post('/', 'store')
                    ->name('store');
                Route::get('/{cat}/edit', 'edit')
                    ->name('edit');
                Route::post('/bulk-destroy', 'bulkDestroy')
                    ->name('bulk-destroy');
                Route::post('/{cat}', 'update')
                    ->name('update');
                Route::delete('/{cat}', 'destroy')
                    ->name('destroy');
            });
        /* End of billing-cats routes */
        //-- Do not delete me :) I'm used for auto-generation admin routes --
    });
