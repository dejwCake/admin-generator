<?php



/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])->group(static function () {
    Route::prefix('admin')->namespace('App\Http\Controllers\Admin')->name('admin/')->group(static function() {
        Route::prefix('admin-users')->name('admin-users/')->group(static function() {
            Route::get('/',                                             'Auth\AdminUsersController@index')->name('index');
            Route::get('/create',                                       'Auth\AdminUsersController@create')->name('create');
            Route::post('/',                                            'Auth\AdminUsersController@store')->name('store');
            Route::get('/{adminUser}/impersonal-login',                 'Auth\AdminUsersController@impersonalLogin')->name('impersonal-login');
            Route::get('/{adminUser}/edit',                             'Auth\AdminUsersController@edit')->name('edit');
            Route::post('/{adminUser}',                                 'Auth\AdminUsersController@update')->name('update');
            Route::delete('/{adminUser}',                               'Auth\AdminUsersController@destroy')->name('destroy');
            Route::get('/{adminUser}/resend-activation',                'Auth\AdminUsersController@resendActivationEmail')->name('resendActivationEmail');
        });
    });
});