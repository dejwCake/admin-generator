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
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);

        return view('brackets/admin-generator::' . $this->view, [
            //globals
            'modelBaseName' => $this->classBaseName,
            'modelNameSpace' => $this->classNamespace,
            // if table name differs from the snake case plural form of the classname,
            // then we need to specify the table name
            'tableName' => $this->tableName !== Str::snake(Str::plural($this->classBaseName))
                ? $this->tableName
                : null,
            'relations' => $this->relations,
            'mediaCollections' => $this->mediaCollections,
            //has
            'hasCarbonProperty' => $columns->hasByMajorType('datetime', 'date'),
            'hasSoftDelete' => $columns->hasByName('deleted_at'),
            'hasPublishedAt' => $columns->hasByName('published_at'),
            'hasTimestamps' => $columns->hasByName('created_at', 'updated_at'),
            //columns
            'allColumns' => $columns->toLegacyCollection(),
            'dates' => $columns->getDates()
                ->toLegacyCollection()
                ->pluck('name'),
            'booleans' => $columns->getBoolean()
                ->toLegacyCollection()
                ->pluck('name'),
            'fillable' => $columns->getFillable()
                ->toLegacyCollection()
                ->pluck('name'),
            'hidden' => $columns->getHidden()
                ->toLegacyCollection()
                ->pluck('name'),
            'translatable' => $columns->getTranslatable()
                ->toLegacyCollection()
                ->pluck('name'),
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
