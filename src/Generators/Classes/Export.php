<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Classes;

use Override;
use Symfony\Component\Console\Input\InputOption;

final class Export extends ClassGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:export';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate an export class';

    /**
     * Path for view
     */
    protected string $view = 'classes.export';

    public function handle(): void
    {
        $force = $this->option('force');
        $template = $this->option('template');

        if ($template !== null) {
            $this->view = sprintf('classes.templates.%s.export', $template);
        }

        if ($this->generateClass($force)) {
            $this->info(sprintf('Generating %s finished', $this->classFullName));
        }
    }

    /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
    #[Override]
    public function generateClassNameFromTable(string $tableName): string
    {
        return $this->exportBaseName;
    }

    #[Override]
    protected function buildClass(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);

        return $this->viewFactory->make(sprintf('brackets/admin-generator::%s', $this->view), [
            //globals
            'exportNamespace' => $this->classNamespace,
            'classBaseName' => $this->exportBaseName,
            'modelFullName' => $this->modelFullName,
            'modelBaseName' => $this->modelBaseName,
            'modelVariableName' => $this->modelVariableName,
            'modelLangFormat' => $this->modelLangFormat,
            //columns
            'columns' => $columns->getToExport(),
        ])->render();
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-with-full-namespace', 'fnm', InputOption::VALUE_OPTIONAL, 'Specify model with full namespace'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating request'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
        ];
    }

    #[Override]
    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return sprintf('%s\Exports', $rootNamespace);
    }
}
