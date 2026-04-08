<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Override;
use Symfony\Component\Console\Input\InputOption;

final class BladeForm extends ResourceGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:blade-form';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate a form blade view';

    /**
     * Path for blade view
     */
    protected string $view = 'resources.blade-form';

    /**
     * Name of view, will be used in directory
     */
    protected string $fileName = '';

    /**
     * Route to process form
     */
    protected ?string $route = null;

    protected string $formJsRelativePath = '';

    public function handle(): void
    {
        $force = (bool) $this->option('force');
        $template = $this->option('template');
        $fileName = $this->option('file-name');
        $this->route = $this->option('route');

        if ($template !== null) {
            $this->view = sprintf('resources.templates.%s.blade-form', $template);
        }

        $belongsToMany = $this->option('belongs-to-many');

        $this->relations = $this->relationBuilder->build($this->tableName, $belongsToMany);

        $this->fileName = $fileName ?: $this->modelViewsDirectory;
        $this->formJsRelativePath = str_replace([DIRECTORY_SEPARATOR, '/', '\\'], '-', $this->fileName);
        if (!$fileName) {
            $this->fileName .= DIRECTORY_SEPARATOR . 'form';
        }

        if (!$this->route) {
            $this->route = $fileName
                ? sprintf('admin/%s', $this->fileName)
                : sprintf('admin/%s/update', $this->resource);
        }

        $path = $this->laravel->resourcePath(sprintf('views/admin/%s.blade.php', $this->fileName));

        $this->generate($path, $force);
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating full form'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['file-name', 'nm', InputOption::VALUE_OPTIONAL, 'Specify a blade file path'],
            ['route', 'r', InputOption::VALUE_OPTIONAL, 'Specify custom route for form'],
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
        ];
    }

    #[Override]
    protected function buildView(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);
        $visibleColumns = $columns->getVisible();

        $hasCreatedByAdminUser = $columns->hasByName('created_by_admin_user_id');
        $hasUpdatedByAdminUser = $columns->hasByName('updated_by_admin_user_id');

        // Columns to display in the main form body (excluding sidebar/system columns)
        $leftFormColumns = $visibleColumns->rejectByName(
            'published_at',
            'created_by_admin_user_id',
            'updated_by_admin_user_id',
        );
        // Right column: only published_at
        $publishedColumns = $visibleColumns->filterByName('published_at');

        // Split media collections: gallery goes right, rest left
        $galleryCollections = $this->mediaCollections->filter(
            static fn (object $collection): bool => $collection->collectionName === 'gallery',
        );

        return $this->viewFactory->make(sprintf('brackets/admin-generator::%s', $this->view), [
            //globals
            'modelVariableName' => $this->modelVariableName,
            'modelJSName' => $this->formJsRelativePath,
            'modelLangFormat' => $this->modelLangFormat,
            'relations' => $this->relations,
            //has
            'hasCreatedByAdminUser' => $hasCreatedByAdminUser,
            'hasUpdatedByAdminUser' => $hasUpdatedByAdminUser,
            'hasTranslatable' => $columns->hasByMajorType('json'),
            'hasPublishedAt' => $columns->hasByName('published_at'),
            'hasWysiwyg' => $leftFormColumns->hasWysiwyg(),
            'hasDateColumns' => $leftFormColumns->hasByMajorType('date'),
            'hasTimeColumns' => $leftFormColumns->hasByMajorType('time'),
            'hasDatetimeColumns' => $leftFormColumns->hasByMajorType('datetime'),
            //columns
            'columns' => $visibleColumns,
            'publishedColumns' => $publishedColumns,
            'profileColumns' => $leftFormColumns->rejectByName('password', 'activated', 'forbidden'),
            //media
            'mediaCollections' => $this->mediaCollections,
            'galleryCollections' => $galleryCollections,
            //other
            'modelLabelColumn' => $columns->getLabelColumn(),
        ])->render();
    }
}
