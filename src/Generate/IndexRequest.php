<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate;

use Symfony\Component\Console\Input\InputOption;

class IndexRequest extends ClassGenerator
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

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $force = $this->option('force');

        if ($this->generateClass($force)) {
            $this->info('Generating ' . $this->classFullName . ' finished');
        }
    }

    public function generateClassNameFromTable(string $tableName): string
    {
        return 'Index' . $this->modelBaseName;
    }

    protected function buildClass(): string
    {
        return view('brackets/admin-generator::index-request', [
            'modelBaseName' => $this->modelBaseName,
            'modelDotNotation' => $this->modelDotNotation,
            'modelWithNamespaceFromDefault' => $this->modelWithNamespaceFromDefault,
            'modelVariableName' => $this->modelVariableName,

            'columnsToQuery' => $this->readColumnsFromTable($this->tableName)->filter(
                static fn ($column) => !($column['type'] === 'text' || $column['name'] === "password" || $column['name'] === "remember_token" || $column['name'] === "slug" || $column['name'] === "created_at" || $column['name'] === "updated_at" || $column['name'] === "deleted_at"),
            )->pluck(
                'name',
            )->toArray(),
        ])->render();
    }

    /** @return array<array<string|int>> */
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating request'],
        ];
    }

    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return $rootNamespace . '\Http\Requests\Admin\\' . $this->modelWithNamespaceFromDefault;
    }
}
