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
        Route::get('/{post:id}/edit', 'edit')
            ->name('edit');
        Route::post('/{post:id}', 'update')
            ->name('update');
        Route::delete('/{post:id}', 'destroy')
            ->name('destroy');
    });
