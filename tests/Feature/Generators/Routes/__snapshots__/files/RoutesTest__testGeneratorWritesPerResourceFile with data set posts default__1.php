<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\PostsController;
use Illuminate\Support\Facades\Route;

Route::prefix('posts')
    ->name('posts/')
    ->controller(PostsController::class)
    ->group(static function (): void {
        Route::get('/', 'index')
            ->name('index');
        Route::get('/create', 'create')
            ->name('create');
        Route::post('/', 'store')
            ->name('store');
        Route::get('/{post}/edit', 'edit')
            ->name('edit');
        Route::post('/bulk-destroy', 'bulkDestroy')
            ->name('bulk-destroy');
        Route::post('/{post}', 'update')
            ->name('update');
        Route::delete('/{post}', 'destroy')
            ->name('destroy');
    });
