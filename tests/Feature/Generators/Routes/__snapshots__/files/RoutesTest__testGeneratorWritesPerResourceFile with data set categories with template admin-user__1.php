<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CategoriesController;
use Illuminate\Support\Facades\Route;

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
        Route::get('/{category:id}/edit', 'edit')
            ->name('edit');
        Route::post('/bulk-destroy', 'bulkDestroy')
            ->name('bulk-destroy');
        Route::post('/{category:id}', 'update')
            ->name('update');
        Route::delete('/{category:id}', 'destroy')
            ->name('destroy');
        Route::get('/{category:id}/impersonal-login', 'impersonalLogin')
            ->name('impersonal-login');
        Route::get('/{category:id}/resend-activation', 'resendActivationEmail')
            ->name('resend-activation-email');
    });
