<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator;

use Brackets\AdminGenerator\Generators\Classes\BulkDestroyRequest;
use Brackets\AdminGenerator\Generators\Classes\Controller;
use Brackets\AdminGenerator\Generators\Classes\DestroyRequest;
use Brackets\AdminGenerator\Generators\Classes\Export;
use Brackets\AdminGenerator\Generators\Classes\Factory;
use Brackets\AdminGenerator\Generators\Classes\ImpersonalLoginRequest;
use Brackets\AdminGenerator\Generators\Classes\IndexRequest;
use Brackets\AdminGenerator\Generators\Classes\Model;
use Brackets\AdminGenerator\Generators\Classes\Permissions;
use Brackets\AdminGenerator\Generators\Classes\StoreRequest;
use Brackets\AdminGenerator\Generators\Classes\UpdateRequest;
use Brackets\AdminGenerator\Generators\FileAppenders\Lang;
use Brackets\AdminGenerator\Generators\FileAppenders\Routes;
use Brackets\AdminGenerator\Generators\Generate;
use Brackets\AdminGenerator\Generators\GenerateAdminUser;
use Brackets\AdminGenerator\Generators\GenerateAdminUserProfile;
use Brackets\AdminGenerator\Generators\GenerateUser;
use Brackets\AdminGenerator\Generators\Resources\Form;
use Brackets\AdminGenerator\Generators\Resources\FullForm;
use Brackets\AdminGenerator\Generators\Resources\Index;
use Illuminate\Support\ServiceProvider;
use Override;

final class AdminGeneratorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'brackets/admin-generator');
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
            Factory::class,
            ImpersonalLoginRequest::class,
            IndexRequest::class,
            Model::class,
            Permissions::class,
            StoreRequest::class,
            UpdateRequest::class,
            //FileAppenders
            Lang::class,
            Routes::class,
            //Resources
            Form::class,
            FullForm::class,
            Index::class,
        ]);
    }
}
