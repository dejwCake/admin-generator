<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Model extends ClassGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:model';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate a model class';

    /**
     * Path for view
     */
    protected string $view = 'model';

    public function handle(): void
    {
        $force = $this->option('force');

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        $template = $this->option('template');
        if ($template !== null) {
            $this->view = 'templates.' . $template . '.model';
        }

        $belongsToMany = $this->option('belongs-to-many');
        if ($belongsToMany !== null) {
            $this->setBelongToManyRelation($belongsToMany);
        }

        if ($this->generateClass($force)) {
            $this->info('Generating ' . $this->classFullName . ' finished');
        }

        // TODO think if we should use ide-helper:models ?
    }

    public function generateClassNameFromTable(string $tableName): string
    {
        return Str::studly(Str::singular($tableName));
    }

    protected function buildClass(): string
    {
        return view('brackets/admin-generator::' . $this->view, [
            'modelBaseName' => $this->classBaseName,
            'modelNameSpace' => $this->classNamespace,

            // if table name differs from the snake case plural form of the classname,
            // then we need to specify the table name
            'tableName' => $this->tableName !== Str::snake(Str::plural($this->classBaseName))
                ? $this->tableName
                : null,

            'dates' => $this->readColumnsFromTable($this->tableName)->filter(
                static fn (array $column): bool => in_array($column['majorType'], ['datetime', 'date'], true),
            )->pluck('name'),
            'fillable' => $this->readColumnsFromTable($this->tableName)->filter(
                static fn (array $column): bool => !in_array(
                    $column['name'],
                    ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'],
                    true,
                ),
            )->pluck('name'),
            'hidden' => $this->readColumnsFromTable($this->tableName)->filter(
                static fn (array $column): bool => in_array($column['name'], ['password', 'remember_token'], true),
            )->pluck('name'),
            'translatable' => $this->readColumnsFromTable($this->tableName)->filter(
                static fn (array $column): bool => $column['majorType'] === 'json',
            )->pluck('name'),
            'timestamps' => $this->readColumnsFromTable($this->tableName)->filter(
                static fn (array $column): bool => in_array($column['name'], ['created_at', 'updated_at'], true),
            )->count() > 0,
            'hasSoftDelete' => $this->readColumnsFromTable($this->tableName)->filter(
                static fn (array $column): bool => $column['name'] === 'deleted_at',
            )->count() > 0,
            'resource' => $this->resource,

            'relations' => $this->relations,
        ])->render();
    }

    /** @return array<array<string|int>> */
    protected function getOptions(): array
    {
        return [
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating model'],
        ];
    }

    /** @return array<array<string|int>> */
    protected function getArguments(): array
    {
        return array_merge(
            parent::getArguments(),
            [
                ['class_name', InputArgument::OPTIONAL, 'Name of the generated class'],
            ],
        );
    }

    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return $rootNamespace . '\Models';
    }
}
