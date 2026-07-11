@php echo "<?php";
@endphp


declare(strict_types=1);

use {{ $controllerFullName }};
use Illuminate\Support\Facades\Route;

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
        Route::get('/{{ '{' }}{{ $modelVariableName }}:id}/edit', 'edit')
            ->name('edit');
@if($hasBulk)
        Route::post('/bulk-destroy', 'bulkDestroy')
            ->name('bulk-destroy');
@endif
        Route::post('/{{ '{' }}{{ $modelVariableName }}:id}', 'update')
            ->name('update');
        Route::delete('/{{ '{' }}{{ $modelVariableName }}:id}', 'destroy')
            ->name('destroy');
@if($hasExport)
        Route::get('/export', 'export')
            ->name('export');
@endif
    });
