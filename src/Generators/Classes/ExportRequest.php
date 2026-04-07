<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Classes;

use Override;
use Symfony\Component\Console\Input\InputOption;

final class ExportRequest extends ClassGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:request:export';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate a Export request class';

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
        return sprintf('Export%s', $this->modelBaseName);
    }

    #[Override]
    protected function buildClass(): string
    {
        return $this->viewFactory->make('brackets/admin-generator::classes.export-request', [
            //globals
            'classBaseName' => $this->classBaseName,
            'classNamespace' => $this->classNamespace,
            'modelDotNotation' => $this->modelDotNotation,
        ])->render();
    }

    /**
     * @return array<array<string|int>>
     */
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
