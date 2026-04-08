<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Brackets\AdminGenerator\Dtos\Media\MediaCollection;
use Illuminate\Support\Str;
use Override;
use Symfony\Component\Console\Input\InputOption;

final class VueForm extends ResourceGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:vue-form';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate a form Vue component';

    protected string $view = 'resources.vue-form';

    protected string $formJsRelativePath = '';

    public function handle(): void
    {
        $force = (bool) $this->option('force');
        $template = $this->option('template');
        $belongsToMany = $this->option('belongs-to-many');
        $media = $this->option('media');
        $fileName = $this->option('file-name');

        if ($template !== null) {
            $this->view = sprintf('resources.templates.%s.vue-form', $template);
        }

        $this->relations = $this->relationBuilder->build($this->tableName, $belongsToMany);

        if ($media !== null && $media !== []) {
            $this->mediaCollections = $this->mediaCollectionBuilder->build($media);
        }

        // When file-name is provided (FullForm mode), use it for JS path
        // Otherwise use modelJSName (Form mode)
        $this->formJsRelativePath = $fileName
            ? str_replace(
                [DIRECTORY_SEPARATOR, '/', '\\'],
                '-',
                $fileName,
            )
            : $this->modelJSName;

        $path = $this->laravel->resourcePath(sprintf('js/admin/%s/Form.vue', $this->formJsRelativePath));

        $this->generate($path, $force);
        $this->registerVueComponent(
            sprintf('%sForm', Str::studly($this->formJsRelativePath)),
            $this->formJsRelativePath,
            'Form.vue',
        );
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
            ['file-name', 'nm', InputOption::VALUE_OPTIONAL, 'Specify a blade file path'],
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

        $validationRules = $visibleColumns->rejectByName(
            'published_at',
            'created_by_admin_user_id',
            'updated_by_admin_user_id',
        )->getFrontendValidationRules();

        return $this->viewFactory->make(sprintf('brackets/admin-generator::%s', $this->view), [
            //globals
            'relations' => $this->relations,
            //has
            'hasCreatedByAdminUser' => $hasCreatedByAdminUser,
            'hasUpdatedByAdminUser' => $hasUpdatedByAdminUser,
            'hasTranslatable' => $columns->hasByMajorType('json'),
            'hasWysiwyg' => $leftFormColumns->hasWysiwyg(),
            'hasPassword' => $leftFormColumns->hasByName('password'),
            'hasEmail' => $leftFormColumns->hasByName('email'),
            'hasBoolColumns' => $leftFormColumns->hasByMajorType('bool'),
            'hasDateColumns' => $leftFormColumns->hasByMajorType('date'),
            'hasTimeColumns' => $leftFormColumns->hasByMajorType('time'),
            'hasDatetimeColumns' => $leftFormColumns->hasByMajorType('datetime'),
            'hasFormInput' => $leftFormColumns->hasFormInput(),
            'hasTextarea' => $leftFormColumns->hasTextarea(),
            'hasLocalizedInput' => $leftFormColumns->hasLocalizedInput(),
            'hasLocalizedWysiwyg' => $leftFormColumns->hasLocalizedWysiwyg(),
            'hasUseAppFormOptions' => count($validationRules) > 0 || $this->relations->hasBelongsTo(),
            //columns
            'leftFormColumns' => $leftFormColumns,
            'publishedColumns' => $publishedColumns,
            'profileColumns' => $leftFormColumns->rejectByName('password', 'activated', 'forbidden'),
            //media
            'mediaCollections' => $this->mediaCollections,
            'leftMediaCollections' => $this->mediaCollections->reject(
                static fn (MediaCollection $mediaCollection): bool => $mediaCollection->collectionName === 'gallery',
            ),
            'galleryCollections' => $galleryCollections,
            'mediaDefaultProp' => sprintf('{%s}', $this->mediaCollections->keys()
                    ->map(static fn (string $key): string => sprintf('%s: {}', $key))
                    ->implode(', ')),
            'mediaCollectionNames' => $this->mediaCollections->keys()
                ->map(static fn (string $key): string => sprintf("'%s'", $key))
                ->implode(', '),
            //other
            'wysiwygTextColumnNames' => $columns->getWysiwygColumnNames(),
            'isUsedTwoColumnsLayout' => $publishedColumns->isNotEmpty()
                || $galleryCollections->isNotEmpty()
                || $hasCreatedByAdminUser
                || $hasUpdatedByAdminUser,
            'validationRules' => $validationRules,
        ])->render();
    }
}
