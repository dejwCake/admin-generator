<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProfileController;
/* Auto-generated admin routes uses */

Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
    ->prefix('admin')
    ->name('admin/')
    ->group(static function (): void {
        /* Auto-generated profile routes */
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
        /* End of profile routes */
        // Do not delete me :) I'm used for auto-generation of admin routes
    });
