<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Classes;

use Illuminate\Support\Str;
use Override;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class Model extends ClassGenerator
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
        $template = $this->option('template');
        $belongsToMany = $this->option('belongs-to-many');
        $media = $this->option('media');

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        if ($template !== null) {
            $this->view = 'templates.' . $template . '.model';
        }

        if ($belongsToMany !== null) {
            $this->setBelongToManyRelation($belongsToMany);
        }

        if ($media !== null && $media !== []) {
            $this->mediaCollections = $this->mediaCollectionBuilder->build($media);
        }

        if ($this->generateClass($force)) {
            $this->info('Generating ' . $this->classFullName . ' finished');
        }
    }

    #[Override]
    public function generateClassNameFromTable(string $tableName): string
    {
        return Str::studly(Str::singular($tableName));
    }

    #[Override]
    protected function buildClass(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName)->toLegacyCollection();

        return view('brackets/admin-generator::' . $this->view, [
            'modelBaseName' => $this->classBaseName,
            'modelNameSpace' => $this->classNamespace,

            // if table name differs from the snake case plural form of the classname,
            // then we need to specify the table name
            'tableName' => $this->tableName !== Str::snake(Str::plural($this->classBaseName))
                ? $this->tableName
                : null,

            'hasCarbonProperty' => $columns->contains(
                static fn (array $column): bool => in_array($column['majorType'], ['datetime', 'date'], true),
            ),
            'hasSoftDelete' => $columns->contains(
                static fn (array $column): bool => $column['name'] === 'deleted_at',
            ),
            'hasPublishedAt' => $columns->contains(
                static fn (array $column): bool => $column['name'] === 'published_at',
            ),
            'allColumns' => $columns,
            'dates' => $columns->filter(
                static fn (array $column): bool => in_array($column['majorType'], ['datetime', 'date'], true),
            )->pluck('name'),
            'booleans' => $columns->filter(
                static fn (array $column): bool => $column['majorType'] === 'bool',
            )->pluck('name'),
            'fillable' => $columns->filter(
                static fn (array $column): bool => !in_array(
                    $column['name'],
                    ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'],
                    true,
                ),
            )->pluck('name'),
            'hidden' => $columns->filter(
                static fn (array $column): bool => in_array($column['name'], ['password', 'remember_token'], true),
            )->pluck('name'),
            'translatable' => $columns->filter(
                static fn (array $column): bool => $column['majorType'] === 'json',
            )->pluck('name'),
            'timestamps' => $columns->filter(
                static fn (array $column): bool => in_array($column['name'], ['created_at', 'updated_at'], true),
            )->count() > 0,
            'relations' => $this->relations,
            'mediaCollections' => $this->mediaCollections,
        ])->render();
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating model'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
            [
                'media',
                'M',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Media collections (format: name:type:disk:maxFiles)',
            ],
        ];
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getArguments(): array
    {
        return array_merge(
            parent::getArguments(),
            [
                ['class_name', InputArgument::OPTIONAL, 'Name of the generated class'],
            ],
        );
    }

    #[Override]
    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return $rootNamespace . '\Models';
    }
}
