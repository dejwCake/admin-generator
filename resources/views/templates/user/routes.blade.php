
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
@if($hasBulk)
                Route::post('/bulk-destroy', 'bulkDestroy')
                    ->name('bulk-destroy');
@endif
                Route::post('/{{ '{' }}{{ $modelVariableName }}}', 'update')
                    ->name('update');
                Route::delete('/{{ '{' }}{{ $modelVariableName }}}', 'destroy')
                    ->name('destroy');
@if($hasExport)
                Route::get('/export', 'export')
                    ->name('export');
@endif
                Route::get('/{{ '{' }}{{ $modelVariableName }}}/resend-verify-email', 'resendVerifyEmail')
                    ->name('resend-verify-email');
            });
        /* End of {{ $resource }} routes */