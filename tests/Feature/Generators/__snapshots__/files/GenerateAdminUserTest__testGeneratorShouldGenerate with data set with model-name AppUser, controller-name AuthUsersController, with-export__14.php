<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\Auth\UsersController;
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
        Route::get('/{user:id}/edit', 'edit')
            ->name('edit');
        Route::post('/bulk-destroy', 'bulkDestroy')
            ->name('bulk-destroy');
        Route::post('/{user:id}', 'update')
            ->name('update');
        Route::delete('/{user:id}', 'destroy')
            ->name('destroy');
        Route::get('/export', 'export')
            ->name('export');
        Route::get('/{user:id}/impersonal-login', 'impersonalLogin')
            ->name('impersonal-login');
        Route::get('/{user:id}/resend-activation', 'resendActivationEmail')
            ->name('resend-activation-email');
    });
