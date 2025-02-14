<?php



/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])->group(static function () {
    Route::prefix('admin')->namespace('App\Http\Controllers\Admin')->name('admin/')->group(static function() {
        Route::prefix('billing-categ-ories')->name('billing-categ-ories/')->group(static function() {
            Route::get('/',                                             'Billing\CategOryController@index')->name('index');
            Route::get('/create',                                       'Billing\CategOryController@create')->name('create');
            Route::post('/',                                            'Billing\CategOryController@store')->name('store');
            Route::get('/{categOry}/edit',                              'Billing\CategOryController@edit')->name('edit');
            Route::post('/bulk-destroy',                                'Billing\CategOryController@bulkDestroy')->name('bulk-destroy');
            Route::post('/{categOry}',                                  'Billing\CategOryController@update')->name('update');
            Route::delete('/{categOry}',                                'Billing\CategOryController@destroy')->name('destroy');
        });
    });
});