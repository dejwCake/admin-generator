<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Brackets\AdminGenerator\Dtos\Media\MediaCollection;
use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
use Illuminate\Support\Collection;
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

    protected string $view = 'vue-form';

    protected string $formJsRelativePath = '';

    public function handle(): void
    {
        $force = (bool) $this->option('force');
        $template = $this->option('template');
        $belongsToMany = $this->option('belongs-to-many');
        $media = $this->option('media');
        $fileName = $this->option('file-name');

        if ($template !== null) {
            $this->view = 'templates.' . $template . '.vue-form';
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

        $path = resource_path('js/admin/' . $this->formJsRelativePath . '/Form.vue');

        $this->generate($path, $force);
        $this->registerInAdminJs();
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

    /** @return array<string, Collection|RelationCollection|ColumnCollection|array<string>|string|bool> */
    private function getCommonViewData(ColumnCollection $columns): array
    {
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

        return [
            //globals
            'modelBaseName' => $this->modelBaseName,
            'modelPlural' => $this->modelPlural,
            'modelVariableName' => $this->modelVariableName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelDotNotation' => $this->modelDotNotation,
            'modelJSName' => $this->formJsRelativePath,
            'modelLangFormat' => $this->modelLangFormat,
            'resource' => $this->resource,
            'mediaCollections' => $this->mediaCollections,
            'relations' => $this->relations,
            //has
            'hasCreatedByAdminUser' => $hasCreatedByAdminUser,
            'hasUpdatedByAdminUser' => $hasUpdatedByAdminUser,
            'hasTranslatable' => $columns->hasByMajorType('json'),
            'hasPublishedAt' => $columns->hasByName('published_at'),
            'hasWysiwyg' => $leftFormColumns->hasWysiwyg(),
            'hasPassword' => $leftFormColumns->hasByName('password'),
            'hasEmail' => $leftFormColumns->hasByName('email'),
            'hasLanguage' => $leftFormColumns->hasByName('language'),
            'hasBoolColumns' => $leftFormColumns->hasByMajorType('bool'),
            'hasDateColumns' => $leftFormColumns->hasByMajorType('date'),
            'hasTimeColumns' => $leftFormColumns->hasByMajorType('time'),
            'hasDatetimeColumns' => $leftFormColumns->hasByMajorType('datetime'),
            'hasFormInput' => $leftFormColumns->hasFormInput(),
            'hasTextarea' => $leftFormColumns->hasTextarea(),
            'hasLocalizedInput' => $leftFormColumns->hasLocalizedInput(),
            'hasLocalizedWysiwyg' => $leftFormColumns->hasLocalizedWysiwyg(),
            //columns
            'columns' => $visibleColumns,
            'leftFormColumns' => $leftFormColumns,
            'leftMediaCollections' => $this->mediaCollections->reject(
                static fn (MediaCollection $mediaCollection): bool => $mediaCollection->collectionName === 'gallery',
            ),
            'publishedColumns' => $publishedColumns,
            'galleryCollections' => $galleryCollections,
            'wysiwygTextColumnNames' => $columns->getWysiwygColumnNames(),

            'isUsedTwoColumnsLayout' => $publishedColumns->isNotEmpty()
                || $galleryCollections->isNotEmpty()
                || $hasCreatedByAdminUser
                || $hasUpdatedByAdminUser,

            'profileColumns' => $leftFormColumns->rejectByName('password', 'activated', 'forbidden'),
        ];
    }

    private function build(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);
        $data = $this->getCommonViewData($columns);

        $data['validationRules'] = $columns->getVisible()->rejectByName(
            'published_at',
            'created_by_admin_user_id',
            'updated_by_admin_user_id',
        )->getFrontendValidationRules();

        $data['mediaDefaultProp'] = '{' . $this->mediaCollections->keys()
            ->map(static fn (string $key): string => "$key: {}")
            ->implode(', ') . '}';
        $data['mediaCollectionNames'] = $this->mediaCollections->keys()
            ->map(static fn (string $key): string => "'$key'")
            ->implode(', ');

        return view('brackets/admin-generator::' . $this->view, $data)->render();
    }

    private function generate(string $path, bool $force): void
    {
        if ($this->alreadyExists($path) && !$force) {
            $this->error('File ' . $path . ' already exists!');

            return;
        }

        if ($this->alreadyExists($path) && $force) {
            $this->warn('File ' . $path . ' already exists! File will be deleted.');
            $this->files->delete($path);
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->build());
        $this->info('Generating ' . $path . ' finished');
    }

    private function registerInAdminJs(): void
    {
        $adminJsPath = resource_path('js/admin/admin.js');

        if (!$this->files->exists($adminJsPath)) {
            $this->warn('File ' . $adminJsPath . ' does not exist, skipping component registration.');

            return;
        }

        $content = $this->files->get($adminJsPath);

        $importMarker = '//-- Do not delete me :) I\'m used for auto-generation js import--';
        $componentMarker = '//-- Do not delete me :) I\'m used for auto-generation component registration--';

        $componentName = Str::studly($this->formJsRelativePath);
        $importLine = "import {$componentName}Form from './{$this->formJsRelativePath}/Form.vue';";
        $componentLine = "app.component('{$componentName}Form', {$componentName}Form);";

        if (!str_contains($content, $importLine)) {
            $content = str_replace($importMarker, $importLine . PHP_EOL . $importMarker, $content);
        }

        if (!str_contains($content, $componentLine)) {
            $content = str_replace($componentMarker, $componentLine . PHP_EOL . $componentMarker, $content);
        }

        $this->files->put($adminJsPath, $content);
    }
}
