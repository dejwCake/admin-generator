@php echo "<?php";
@endphp


declare(strict_types=1);

use {{ $controllerFullName }};
@if($hasTrash || $hasBulkTrash)
use {{ $trashControllerFullName }};
@endif
use Illuminate\Support\Facades\Route;
@if($hasTrash || $hasBulkTrash)

Route::prefix('{{ $resource }}')
    ->name('{{ $resource }}/')
    ->controller({{ $trashControllerBaseName }}::class)
    ->group(static function (): void {
@if($hasBulkTrash)
        Route::post('/bulk-force-destroy', 'bulkForceDestroy')
            ->name('bulk-force-destroy');
        Route::post('/bulk-restore', 'bulkRestore')
            ->name('bulk-restore');
@endif
@if($hasTrash)
        Route::delete('/{{ '{' }}{{ $modelVariableName }}:id}/force', 'forceDestroy')
            ->name('force-destroy')
            ->withTrashed();
        Route::post('/{{ '{' }}{{ $modelVariableName }}:id}/restore', 'restore')
            ->name('restore')
            ->withTrashed();
@endif
    });
@endif

Route::prefix('{{ $resource }}')
    ->name('{{ $resource }}/')
    ->controller({{ $controllerBaseName }}::class)
    ->group(static function (): void {
        Route::get('/', 'index')
            ->name('index');
@if($hasCreate)
        Route::get('/create', 'create')
            ->name('create');
        Route::post('/', 'store')
            ->name('store');
@endif
@if($hasEdit)
        Route::get('/{{ '{' }}{{ $modelVariableName }}:id}/edit', 'edit')
            ->name('edit');
@endif
@if($hasBulkDelete)
        Route::post('/bulk-destroy', 'bulkDestroy')
            ->name('bulk-destroy');
@endif
@if($hasEdit)
        Route::post('/{{ '{' }}{{ $modelVariableName }}:id}', 'update')
            ->name('update');
@endif
@if($hasDelete)
        Route::delete('/{{ '{' }}{{ $modelVariableName }}:id}', 'destroy')
            ->name('destroy');
@endif
@if($hasExport)
        Route::get('/export', 'export')
            ->name('export');
@endif
@if($hasShow)
        Route::get('/{{ '{' }}{{ $modelVariableName }}:id}', 'show')
            ->name('show');
@endif
    });
