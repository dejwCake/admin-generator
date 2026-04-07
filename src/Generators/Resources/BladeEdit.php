<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Override;
use Symfony\Component\Console\Input\InputOption;

final class BladeEdit extends ResourceGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:blade-edit';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate an edit blade view';

    protected string $view = 'resources.blade-edit';

    public function handle(): void
    {
        $force = (bool) $this->option('force');
        $template = $this->option('template');
        $belongsToMany = $this->option('belongs-to-many');
        $media = $this->option('media');

        if ($template !== null) {
            $this->view = 'resources.templates.' . $template . '.blade-edit';
        }

        $this->relations = $this->relationBuilder->build($this->tableName, $belongsToMany);

        if ($media !== null && $media !== []) {
            $this->mediaCollections = $this->mediaCollectionBuilder->build($media);
        }

        $path = resource_path('views/admin/' . $this->modelViewsDirectory . '/edit.blade.php');

        $this->generate($path, $force);
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating form'],
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

    #[Override]
    protected function buildView(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);
        $visibleColumns = $columns->getVisible();

        $formColumns = $visibleColumns->rejectByName(
            'published_at',
            'created_by_admin_user_id',
            'updated_by_admin_user_id',
        );
        $publishedColumns = $visibleColumns->filterByName('published_at');
        $galleryCollections = $this->mediaCollections->filter(
            static fn (object $collection): bool => $collection->collectionName === 'gallery',
        );

        return view('brackets/admin-generator::' . $this->view, [
            //globals
            'modelVariableName' => $this->modelVariableName,
            'modelJSName' => $this->modelJSName,
            'modelLangFormat' => $this->modelLangFormat,
            'mediaCollections' => $this->mediaCollections,
            'relations' => $this->relations,
            //has
            'hasCreatedByAdminUser' => $columns->hasByName('created_by_admin_user_id'),
            'hasUpdatedByAdminUser' => $columns->hasByName('updated_by_admin_user_id'),
            'hasTranslatable' => $columns->hasByMajorType('json'),
            'hasPublishedAt' => $columns->hasByName('published_at'),
            'hasWysiwyg' => $formColumns->hasWysiwyg(),
            'hasDateColumns' => $formColumns->hasByMajorType('date'),
            'hasTimeColumns' => $formColumns->hasByMajorType('time'),
            'hasDatetimeColumns' => $formColumns->hasByMajorType('datetime'),
            //columns
            'columns' => $visibleColumns,
            'publishedColumns' => $publishedColumns,
            'galleryCollections' => $galleryCollections,
            //other
            'modelLabelColumn' => $columns->getLabelColumn(),
        ])->render();
    }
}
