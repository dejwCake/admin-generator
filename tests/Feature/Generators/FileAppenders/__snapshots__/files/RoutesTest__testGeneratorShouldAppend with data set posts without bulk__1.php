<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PostsController;


//-- Do not delete me :) I'm used for auto-generation admin routes uses --

Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
    ->prefix('admin')
    ->name('admin/')
    ->group(static function (): void {
        /* Auto-generated posts routes */
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
                Route::post('/{post}', 'update')
                    ->name('update');
                Route::delete('/{post}', 'destroy')
                    ->name('destroy');
            });
        /* End of posts routes */
        //-- Do not delete me :) I'm used for auto-generation admin routes --
    });
