<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator;

use Illuminate\Support\ServiceProvider;

class AdminGeneratorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            GenerateAdmin::class,
            GenerateAdminProfile::class,
            GenerateAdminUser::class,
            GenerateUser::class,
            Generate\Model::class,
            Generate\Controller::class,
            Generate\ViewIndex::class,
            Generate\ViewForm::class,
            Generate\ViewFullForm::class,
            Generate\Factory::class,
            Generate\Routes::class,
            Generate\IndexRequest::class,
            Generate\StoreRequest::class,
            Generate\UpdateRequest::class,
            Generate\DestroyRequest::class,
            Generate\ImpersonalLoginRequest::class,
            Generate\BulkDestroyRequest::class,
            Generate\Lang::class,
            Generate\Permissions::class,
            Generate\Export::class,
        ]);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'brackets/admin-generator');
    }

    public function register(): void
    {
        //do nothing
    }
}
