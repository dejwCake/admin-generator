<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CategoriesController;
use App\Http\Controllers\Admin\CategoriesTrashController;
use Illuminate\Support\Facades\Route;

Route::prefix('categories')
    ->name('categories/')
    ->controller(CategoriesTrashController::class)
    ->group(static function (): void {
        Route::delete('/{category:id}/force', 'forceDestroy')
            ->name('force-destroy')
            ->withTrashed();
        Route::post('/{category:id}/restore', 'restore')
            ->name('restore')
            ->withTrashed();
    });

Route::prefix('categories')
    ->name('categories/')
    ->controller(CategoriesController::class)
    ->group(static function (): void {
        Route::get('/', 'index')
            ->name('index');
        Route::delete('/{category:id}', 'destroy')
            ->name('destroy');
    });
