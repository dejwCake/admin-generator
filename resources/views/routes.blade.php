
/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
    ->prefix('admin')
    ->name('admin/')
    ->group(static function (): void {
        Route::prefix('{{ $resource }}')
            ->name('{{ $resource }}/')
            ->group(static function(): void {
                Route::get('/', [\App\Http\Controllers\Admin\{{ $controllerPartiallyFullName }}::class, 'index'])
                    ->name('index');
                Route::get('/create', [\App\Http\Controllers\Admin\{{ $controllerPartiallyFullName }}::class, 'create'])
                    ->name('create');
                Route::post('/', [\App\Http\Controllers\Admin\{{ $controllerPartiallyFullName }}::class, 'store'])
                    ->name('store');
                Route::get('/{{{ $modelVariableName }}}/edit', [\App\Http\Controllers\Admin\{{ $controllerPartiallyFullName }}::class, 'edit'])
                    ->name('edit');
@if(!$withoutBulk)
                Route::post('/bulk-destroy', [\App\Http\Controllers\Admin\{{ $controllerPartiallyFullName }}::class, 'bulkDestroy'])
                    ->name('bulk-destroy');
@endif
                Route::post('/{{{ $modelVariableName }}}', [\App\Http\Controllers\Admin\{{ $controllerPartiallyFullName }}::class, 'update'])
                    ->name('update');
                Route::delete('/{{{ $modelVariableName }}}', [\App\Http\Controllers\Admin\{{ $controllerPartiallyFullName }}::class, 'destroy'])
                    ->name('destroy');
@if($export)
                Route::get('/export', [\App\Http\Controllers\Admin\{{ $controllerPartiallyFullName }}::class, 'export'])
                    ->name('export');
@endif
            });
    });