<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator;

use Brackets\AdminGenerator\Generators\Classes\BulkDestroyRequest;
use Brackets\AdminGenerator\Generators\Classes\Controller;
use Brackets\AdminGenerator\Generators\Classes\DestroyRequest;
use Brackets\AdminGenerator\Generators\Classes\Export;
use Brackets\AdminGenerator\Generators\Classes\ExportRequest;
use Brackets\AdminGenerator\Generators\Classes\Factory;
use Brackets\AdminGenerator\Generators\Classes\ImpersonalLoginRequest;
use Brackets\AdminGenerator\Generators\Classes\IndexRequest;
use Brackets\AdminGenerator\Generators\Classes\Model;
use Brackets\AdminGenerator\Generators\Classes\Permissions;
use Brackets\AdminGenerator\Generators\Classes\StoreRequest;
use Brackets\AdminGenerator\Generators\Classes\UpdateRequest;
use Brackets\AdminGenerator\Generators\FileAppenders\Lang;
use Brackets\AdminGenerator\Generators\Generate;
use Brackets\AdminGenerator\Generators\GenerateAdminUser;
use Brackets\AdminGenerator\Generators\GenerateAdminUserProfile;
use Brackets\AdminGenerator\Generators\GenerateUser;
use Brackets\AdminGenerator\Generators\Resources\BladeCreate;
use Brackets\AdminGenerator\Generators\Resources\BladeEdit;
use Brackets\AdminGenerator\Generators\Resources\BladeForm;
use Brackets\AdminGenerator\Generators\Resources\BladeIndex;
use Brackets\AdminGenerator\Generators\Resources\VueForm;
use Brackets\AdminGenerator\Generators\Resources\VueListing;
use Brackets\AdminGenerator\Generators\Routes\Routes;
use Illuminate\Support\ServiceProvider;
use Override;

final class AdminGeneratorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(sprintf('%s/../resources/views', __DIR__), 'brackets/admin-generator');
    }

    #[Override]
    public function register(): void
    {
        $this->commands([
            //General
            Generate::class,
            GenerateAdminUserProfile::class,
            GenerateAdminUser::class,
            GenerateUser::class,
            //Classes
            BulkDestroyRequest::class,
            Controller::class,
            DestroyRequest::class,
            Export::class,
            ExportRequest::class,
            Factory::class,
            ImpersonalLoginRequest::class,
            IndexRequest::class,
            Model::class,
            Permissions::class,
            StoreRequest::class,
            UpdateRequest::class,
            //FileAppenders
            Lang::class,
            //Resources
            BladeCreate::class,
            BladeEdit::class,
            BladeForm::class,
            BladeIndex::class,
            VueForm::class,
            VueListing::class,
            //Routes
            Routes::class,
        ]);
    }
}
