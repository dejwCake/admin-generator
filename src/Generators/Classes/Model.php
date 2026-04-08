<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Classes;

use Brackets\AdminGenerator\Naming;
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
    protected string $view = 'classes.model';

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
            $this->view = sprintf('classes.templates.%s.model', $template);
        }

        $this->relations = $this->relationBuilder->build($this->tableName, $belongsToMany);

        if ($media !== null && $media !== []) {
            $this->mediaCollections = $this->mediaCollectionBuilder->build($media);
        }

        if ($this->generateClass($force)) {
            $this->info(sprintf('Generating %s finished', $this->classFullName));
        }
    }

    #[Override]
    public function generateClassNameFromTable(string $tableName): string
    {
        return Naming::modelName($tableName);
    }

    #[Override]
    protected function buildClass(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);

        return $this->viewFactory->make(sprintf('brackets/admin-generator::%s', $this->view), [
            //globals
            'modelBaseName' => $this->classBaseName,
            'modelNameSpace' => $this->classNamespace,
            // if table name differs from the snake case plural form of the classname,
            // then we need to specify the table name
            'tableName' => $this->tableName !== Str::snake(Str::plural($this->classBaseName))
                ? $this->tableName
                : null,
            'relations' => $this->relations,
            //has
            'hasCarbonProperty' => $columns->hasByMajorType('datetime', 'date'),
            'hasSoftDelete' => $columns->hasByName('deleted_at'),
            'hasPublishedAt' => $columns->hasByName('published_at'),
            'hasTimestamps' => $columns->hasByName('created_at', 'updated_at'),
            'hasRoles' => $this->relations->hasRelatedTableInBelongsToMany('roles'),
            //columns
            'columns' => $columns,
            'dateColumns' => $columns->getDates(),
            'booleanColumns' => $columns->getBoolean(),
            'fillableColumns' => $columns->getFillable(),
            'hiddenColumns' => $columns->getHidden(),
            'translatableColumns' => $columns->getTranslatable(),
            //media
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
        return sprintf('%s\Models', $rootNamespace);
    }
}
