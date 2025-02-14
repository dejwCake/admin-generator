<?php



/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])->group(static function () {
    Route::prefix('admin')->namespace('App\Http\Controllers\Admin')->name('admin/')->group(static function() {
        Route::get('/profile',                                      'Auth\ProfileController@editProfile')->name('edit-profile');
        Route::post('/profile',                                     'Auth\ProfileController@updateProfile')->name('update-profile');
        Route::get('/password',                                     'Auth\ProfileController@editPassword')->name('edit-password');
        Route::post('/password',                                    'Auth\ProfileController@updatePassword')->name('update-password');
    });
});