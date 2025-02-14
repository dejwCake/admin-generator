<?php



/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])->group(static function () {
    Route::prefix('admin')->namespace('App\Http\Controllers\Admin')->name('admin/')->group(static function() {
        Route::prefix('users')->name('users/')->group(static function() {
            Route::get('/',                                             'Auth\UsersController@index')->name('index');
            Route::get('/create',                                       'Auth\UsersController@create')->name('create');
            Route::post('/',                                            'Auth\UsersController@store')->name('store');
            Route::get('/{user}/impersonal-login',                      'Auth\UsersController@impersonalLogin')->name('impersonal-login');
            Route::get('/{user}/edit',                                  'Auth\UsersController@edit')->name('edit');
            Route::post('/{user}',                                      'Auth\UsersController@update')->name('update');
            Route::delete('/{user}',                                    'Auth\UsersController@destroy')->name('destroy');
            Route::get('/{user}/resend-activation',                     'Auth\UsersController@resendActivationEmail')->name('resendActivationEmail');
        });
    });
});