
/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
    ->prefix('admin')
    ->name('admin/')
    ->group(static function (): void {
        Route::get('/profile', [\App\Http\Controllers\Admin\{{ $controllerPartiallyFullName }}::class, 'editProfile'])
            ->name('edit-profile');
        Route::post('/profile', [\App\Http\Controllers\Admin\{{ $controllerPartiallyFullName }}::class, 'updateProfile'])
            ->name('update-profile');
        Route::get('/password', [\App\Http\Controllers\Admin\{{ $controllerPartiallyFullName }}::class, 'editPassword'])
            ->name('edit-password');
        Route::post('/password', [\App\Http\Controllers\Admin\{{ $controllerPartiallyFullName }}::class, 'updatePassword'])
            ->name('update-password');
    });