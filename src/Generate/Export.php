<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate;

use Symfony\Component\Console\Input\InputOption;

class Export extends ClassGenerator
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
    protected string $view = 'export';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $force = $this->option('force');

        if (!empty($template = $this->option('template'))) {
            $this->view = 'templates.' . $template . '.export';
        }

        if ($this->generateClass($force)) {
            $this->info('Generating ' . $this->classFullName . ' finished');
        }
    }

    public function generateClassNameFromTable(string $tableName): string
    {
        return $this->exportBaseName;
    }

    protected function buildClass(): string
    {
        return view('brackets/admin-generator::' . $this->view, [
            'exportNamespace' => $this->classNamespace,
            'modelFullName' => $this->modelFullName,
            'classBaseName' => $this->exportBaseName,
            'modelBaseName' => $this->modelBaseName,
            'modelVariableName' => $this->modelVariableName,
            'modelLangFormat' => $this->modelLangFormat,
            'columnsToExport' => $this->readColumnsFromTable($this->tableName)->filter(
                static fn ($column) => !($column['name'] === "password" || $column['name'] === "remember_token" || $column['name'] === "updated_at" || $column['name'] === "created_at" || $column['name'] === "deleted_at"),
            )->pluck(
                'name',
            )->toArray(),
        ])->render();
    }

    /** @return array<array<string|int>> */
    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating request'],
            ['model-with-full-namespace', 'fnm', InputOption::VALUE_OPTIONAL, 'Specify model with full namespace'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
        ];
    }

    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return $rootNamespace . '\Exports';
    }
}
