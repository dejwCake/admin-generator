
/* Auto-generated admin routes */
Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
    ->prefix('admin')
    ->name('admin/')
    ->group(static function (): void {
        Route::prefix('{{ $resource }}')
            ->name('{{ $resource }}/')
            ->group(static function (): void {
                Route::get(
                    '/',
                    [\{{ $controllerFullName }}::class, 'index'],
                )->name('index');
                Route::get(
                    '/create',
                    [\{{ $controllerFullName }}::class, 'create'],
                )->name('create');
                Route::post(
                    '/',
                    [\{{ $controllerFullName }}::class, 'store'],
                )->name('store');
                Route::get(
                    '/{{ '{' }}{{ $modelVariableName }}}/edit',
                    [\{{ $controllerFullName }}::class, 'edit'],
                )->name('edit');
@if(!$withoutBulk)
                Route::post(
                    '/bulk-destroy',
                    [\{{ $controllerFullName }}::class, 'bulkDestroy'],
                )->name('bulk-destroy');
@endif
                Route::post(
                    '/{{ '{' }}{{ $modelVariableName }}}',
                    [\{{ $controllerFullName }}::class, 'update'],
                )->name('update');
                Route::delete(
                    '/{{ '{' }}{{ $modelVariableName }}}',
                    [\{{ $controllerFullName }}::class, 'destroy'],
                )->name('destroy');
@if($export)
                Route::get(
                    '/export',
                    [\{{ $controllerFullName }}::class, 'export'],
                )->name('export');
@endif
            });
    });