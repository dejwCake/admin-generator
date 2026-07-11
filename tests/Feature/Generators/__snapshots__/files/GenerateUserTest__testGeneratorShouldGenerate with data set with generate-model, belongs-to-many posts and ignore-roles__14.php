<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\UsersController;
use Illuminate\Support\Facades\Route;

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
