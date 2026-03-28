
        /* Auto-generated {{ $resource }} routes */
        Route::prefix('{{ $resource }}')
            ->name('{{ $resource }}/')
            ->controller({{ $controllerBaseName }}::class)
            ->group(static function (): void {
                Route::get('/', 'index')
                    ->name('index');
                Route::get('/create', 'create')
                    ->name('create');
                Route::post('/', 'store')
                    ->name('store');
                Route::get('/{{ '{' }}{{ $modelVariableName }}}/edit', 'edit')
                    ->name('edit');
@if(!$withoutBulk)
                Route::post('/bulk-destroy', 'bulkDestroy')
                    ->name('bulk-destroy');
@endif
                Route::post('/{{ '{' }}{{ $modelVariableName }}}', 'update')
                    ->name('update');
                Route::delete('/{{ '{' }}{{ $modelVariableName }}}', 'destroy')
                    ->name('destroy');
@if($export)
                Route::get('/export', 'export')
                    ->name('export');
@endif
            });
        /* End of {{ $resource }} routes */