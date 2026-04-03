<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Classes;

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
    protected string $view = 'factory';

    public function handle(): void
    {
        $force = $this->option('force');
        $template = $this->option('template');
        $seed = $this->option('seed');

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        if ($template !== null) {
            $this->view = 'templates.' . $template . '.factory';
        }

        if ($this->generateClass($force)) {
            $this->info('Generating ' . $this->classFullName . ' finished');
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
        return $this->modelBaseName . 'Factory';
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

        return view('brackets/admin-generator::' . $this->view, [
            //global
            'namespace' => $this->classNamespace,
            'modelFullName' => $this->modelFullName,
            'modelBaseName' => $this->modelBaseName,
            //has
            'hasPassword' => $columns->hasByName('password'),
            'hasEmailVerified' => $columns->hasByName('email_verified_at'),
            'hasPublishedAt' => $columns->hasByName('published_at'),
            //columns
            'translatableColumns' => $columns->getTranslatable()
                ->toLegacyCollection(),
            'standardColumns' => $columns->getNonTranslatable()
                ->toLegacyCollection(),
            'booleanColumns' => $columns->getBoolean()
                ->toLegacyCollection(),
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
