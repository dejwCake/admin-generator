
/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
    ->prefix('admin')
    ->name('admin/')
    ->group(static function (): void {
        Route::get(
            '/profile',
            [\{{ $controllerFullName }}::class, 'editProfile'],
        )->name('edit-profile');
        Route::post(
            '/profile',
            [\{{ $controllerFullName }}::class, 'updateProfile'],
        )->name('update-profile');
        Route::get(
            '/password',
            [\{{ $controllerFullName }}::class, 'editPassword'],
        )->name('edit-password');
        Route::post(
            '/password',
            [\{{ $controllerFullName }}::class, 'updatePassword'],
        )->name('update-password');
    });