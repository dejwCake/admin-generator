<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\PostsController;
use Illuminate\Support\Facades\Route;

Route::prefix('articles')
    ->name('articles/')
    ->controller(PostsController::class)
    ->group(static function (): void {
        Route::get('/', 'index')
            ->name('index');
        Route::get('/create', 'create')
            ->name('create');
        Route::post('/', 'store')
            ->name('store');
        Route::get('/{article}/edit', 'edit')
            ->name('edit');
        Route::post('/bulk-destroy', 'bulkDestroy')
            ->name('bulk-destroy');
        Route::post('/{article}', 'update')
            ->name('update');
        Route::delete('/{article}', 'destroy')
            ->name('destroy');
    });
