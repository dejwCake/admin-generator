<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Classes;

use Override;
use Symfony\Component\Console\Input\InputOption;

/**
 * The request classes of the trash actions (restore, force destroy and their bulk variants),
 * which differ only in their name, template and permission.
 */
abstract class TrashRequest extends ClassGenerator
{
    /**
     * Path for view
     */
    protected string $view;

    /**
     * Prefix of the generated class name, e.g. Restore for RestoreCategory
     */
    protected string $classPrefix;

    public function handle(): void
    {
        $force = $this->option('force');

        if (!$this->columnCollectionBuilder->build($this->tableName)->hasByName('deleted_at')) {
            $this->warn(sprintf('Skipping %s: %s has no deleted_at column.', $this->classBaseName, $this->tableName));

            return;
        }

        if ($this->generateClass($force)) {
            $this->info(sprintf('Generating %s finished', $this->classFullName));
        }
    }

    /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
    #[Override]
    public function generateClassNameFromTable(string $tableName): string
    {
        return sprintf('%s%s', $this->classPrefix, $this->modelBaseName);
    }

    #[Override]
    protected function buildClass(): string
    {
        return $this->viewFactory->make(sprintf('brackets/admin-generator::%s', $this->view), [
            //globals
            'classBaseName' => $this->classBaseName,
            'classNamespace' => $this->classNamespace,
            'modelBaseName' => $this->modelBaseName,
            'modelDotNotation' => $this->modelDotNotation,
            'modelVariableName' => $this->modelVariableName,
            'modelFullName' => $this->modelFullName,
        ])->render();
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['model-with-full-namespace', 'fnm', InputOption::VALUE_OPTIONAL, 'Specify model with full namespace'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating request'],
        ];
    }

    #[Override]
    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return sprintf('%s\Http\Requests\Admin\%s', $rootNamespace, $this->modelWithNamespaceFromDefault);
    }
}
