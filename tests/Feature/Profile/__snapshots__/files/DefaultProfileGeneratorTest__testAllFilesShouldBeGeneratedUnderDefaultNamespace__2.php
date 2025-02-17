<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
    ->prefix('admin')
    ->name('admin/')
    ->group(static function (): void {
        Route::get(
            '/profile',
            [\App\Http\Controllers\Admin\ProfileController::class, 'editProfile'],
        )->name('edit-profile');
        Route::post(
            '/profile',
            [\App\Http\Controllers\Admin\ProfileController::class, 'updateProfile'],
        )->name('update-profile');
        Route::get(
            '/password',
            [\App\Http\Controllers\Admin\ProfileController::class, 'editPassword'],
        )->name('edit-password');
        Route::post(
            '/password',
            [\App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'],
        )->name('update-password');
    });
