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
        Route::get('/{category}/edit', 'edit')
            ->name('edit');
        Route::post('/bulk-destroy', 'bulkDestroy')
            ->name('bulk-destroy');
        Route::post('/{category}', 'update')
            ->name('update');
        Route::delete('/{category}', 'destroy')
            ->name('destroy');
        Route::get('/{category}/resend-verify-email', 'resendVerifyEmail')
            ->name('resend-verify-email');
        Route::get('/{category}/impersonal-login', 'impersonalLogin')
            ->name('impersonal-login');
    });
