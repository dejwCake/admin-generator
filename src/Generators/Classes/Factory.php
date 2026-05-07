<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Classes;

use Illuminate\Support\Str;
use Override;
use Symfony\Component\Console\Input\InputOption;

final class Factory extends ClassGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:factory';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate a new factory class';

    /**
     * Path for view
     */
    protected string $view = 'classes.factory';

    public function handle(): void
    {
        $force = $this->option('force');
        $template = $this->option('template');
        $seed = $this->option('seed');

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        if ($template !== null) {
            $this->view = sprintf('classes.templates.%s.factory', $template);
        }

        if ($this->generateClass($force)) {
            $this->info(sprintf('Generating %s finished', $this->classFullName));
        }

        if ($seed) {
            $this->info('Seeding testing data');
            $this->modelFullName::factory()->count(50)->create();
        }
    }

    /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
    #[Override]
    public function generateClassNameFromTable(string $tableName): string
    {
        $appNamespace = trim($this->laravel->getNamespace(), '\\');
        $modelsPrefix = sprintf('%s\\Models\\', $appNamespace);
        $appPrefix = sprintf('%s\\', $appNamespace);

        if (Str::startsWith($this->modelFullName, $modelsPrefix)) {
            $sub = Str::after($this->modelFullName, $modelsPrefix);
        } elseif (Str::startsWith($this->modelFullName, $appPrefix)) {
            $sub = Str::after($this->modelFullName, $appPrefix);
        } else {
            $sub = $this->modelFullName;
        }

        return sprintf('%sFactory', $sub);
    }

    /**
     * Get the root namespace for the class.
     */
    #[Override]
    public function rootNamespace(): string
    {
        return 'Database';
    }

    #[Override]
    protected function buildClass(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);

        return $this->viewFactory->make(sprintf('brackets/admin-generator::%s', $this->view), [
            //global
            'namespace' => $this->classNamespace,
            'modelFullName' => $this->modelFullName,
            'modelBaseName' => $this->modelBaseName,
            //has
            'hasPassword' => $columns->hasByName('password'),
            'hasEmailVerifiedAt' => $columns->hasByName('email_verified_at'),
            'hasPublishedAt' => $columns->hasByName('published_at'),
            'hasCreatedByAdminUser' => $columns->hasByName('created_by_admin_user_id'),
            'hasUpdatedByAdminUser' => $columns->hasByName('updated_by_admin_user_id'),
            //columns
            'columns' => $columns,
        ])->render();
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['model-with-full-namespace', 'fnm', InputOption::VALUE_OPTIONAL, 'Specify model with full namespace'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating factory'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['seed', 's', InputOption::VALUE_OPTIONAL, 'Seeds the table with fake data'],
        ];
    }

    /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
    #[Override]
    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return 'Database\Factories';
    }
}
