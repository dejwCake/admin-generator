
        /* Auto-generated {{ $resource }} routes */
        Route::controller({{ $controllerBaseName }}::class)
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
        /* End of {{ $resource }} routes */