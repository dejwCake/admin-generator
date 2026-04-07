<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Classes;

use Override;
use Symfony\Component\Console\Input\InputOption;

final class IndexRequest extends ClassGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:request:index';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate an Index request class';

    public function handle(): void
    {
        $force = $this->option('force');

        if ($this->generateClass($force)) {
            $this->info(sprintf('Generating %s finished', $this->classFullName));
        }
    }

    /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
    #[Override]
    public function generateClassNameFromTable(string $tableName): string
    {
        return sprintf('Index%s', $this->modelBaseName);
    }

    #[Override]
    protected function buildClass(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);

        return $this->viewFactory->make('brackets/admin-generator::classes.index-request', [
            //globals
            'classBaseName' => $this->classBaseName,
            'classNamespace' => $this->classNamespace,
            'modelDotNotation' => $this->modelDotNotation,
            //columns
            'columns' => $columns->getToQuery(),
        ])->render();
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating request'],
        ];
    }

    #[Override]
    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return sprintf('%s\Http\Requests\Admin\%s', $rootNamespace, $this->modelWithNamespaceFromDefault);
    }
}
