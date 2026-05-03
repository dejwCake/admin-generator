<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ProfileController;
use Illuminate\Support\Facades\Route;

Route::controller(ProfileController::class)
    ->group(static function (): void {
        Route::get('/profile', 'editProfile')
            ->name('edit-profile');
        Route::post('/profile', 'updateProfile')
            ->name('update-profile');
        Route::get('/password', 'editPassword')
            ->name('edit-password');
        Route::post('/password', 'updatePassword')
            ->name('update-password');
    });
