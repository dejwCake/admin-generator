<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Brackets\AdminGenerator\Dtos\Media\MediaCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Override;
use Symfony\Component\Console\Input\InputOption;

final class Form extends ResourceGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:form';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate create and edit view templates';

    /**
     * Path for create view
     */
    protected string $create = 'create';

    /**
     * Path for edit view
     */
    protected string $edit = 'edit';

    /**
     * Path for Vue form component view
     */
    protected string $formVue = 'form-vue';

    /** @return array<string, string|bool|array<string>> */
    private static function enrichWithForeignKey(array $col, Collection $foreignKeys): array
    {
        if ($col['isForeignKey'] ?? false) {
            $fk = $foreignKeys->keyBy('column')[$col['name']] ?? null;
            if ($fk !== null) {
                $col['foreignKeyOptionsName'] = $fk['optionsPropName'];
                $col['foreignKeyLabel'] = $fk['foreignKeyLabel'];
            }
        }

        return $col;
    }

    public function handle(): void
    {
        $force = (bool) $this->option('force');
        $template = $this->option('template');
        $belongsToMany = $this->option('belongs-to-many');
        $media = $this->option('media');

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        if ($template !== null) {
            $this->create = 'templates.' . $template . '.create';
            $this->edit = 'templates.' . $template . '.edit';
            $this->formVue = 'templates.' . $template . '.form-vue';
        }

        $this->relations = $belongsToMany !== null
            ? $this->belongsToManyRelationBuilder->build($belongsToMany, $this->tableName)
            : $this->belongsToManyRelationBuilder->detectForTable($this->tableName);

        if ($media !== null && $media !== []) {
            $this->mediaCollections = $this->mediaCollectionBuilder->build($media);
        }

        $this->generateCreate($force);
        $this->generateEdit($force);
        $this->generateFormVue($force);
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
        ];
    }

    //TODO move to ColumnCollectionBuilder
    /** @return Collection<int, array<string, string>> */
    private function detectForeignKeys(Collection $columns): Collection
    {
        return $columns->filter(
            static fn (array $col): bool => str_ends_with($col['name'], '_id')
                && !in_array($col['name'], ['created_by_admin_user_id', 'updated_by_admin_user_id'], true),
        )->map(function (array $col): array {
            $name = $col['name'];
            $relatedTable = Str::plural(Str::beforeLast($name, '_id'));
            $relatedModel = Str::studly(Str::singular($relatedTable));
            $optionsPropName = Str::camel(Str::singular($relatedTable)) . 'Options';

            $foreignKeyLabel = $this->columnCollectionBuilder->build($relatedTable)
                ->getLabelColumn();

            return [
                'column' => $name,
                'relatedTable' => $relatedTable,
                'relatedModel' => $relatedModel,
                'optionsPropName' => $optionsPropName,
                'foreignKeyLabel' => $foreignKeyLabel,
            ];
        })->values();
    }

    /** @return array<string, Collection|array<string>|string|bool> */
    private function getCommonViewData(ColumnCollection $columns): array
    {
        $visibleColumns = $columns->getVisible();
        $foreignKeys = $this->detectForeignKeys($visibleColumns->toLegacyCollection());

        $hasCreatedByAdminUser = $columns->hasByName('created_by_admin_user_id');
        $hasUpdatedByAdminUser = $columns->hasByName('updated_by_admin_user_id');

        // Right column: only published_at
        $rightFormColumns = $visibleColumns->filterByName('published_at');
        // Columns to display in the main form body (excluding sidebar/system columns)
        $leftFormColumns = $visibleColumns->rejectByName(
            'published_at',
            'created_by_admin_user_id',
            'updated_by_admin_user_id',
        );

        // Split media collections: gallery goes right, rest left
        $rightMediaCollections = $this->mediaCollections->filter(
            static fn (object $collection): bool => $collection->collectionName === 'gallery',
        );

        //TODO move to ColumnCollectionBuilder
        $leftFormColumnsLegacy = $leftFormColumns->toLegacyCollection()->map(
            static fn (array $col): array => self::enrichWithForeignKey($col, $foreignKeys),
        );

        return [
            //globals
            'modelBaseName' => $this->modelBaseName,
            'modelPlural' => $this->modelPlural,
            'modelVariableName' => $this->modelVariableName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelDotNotation' => $this->modelDotNotation,
            'modelJSName' => $this->modelJSName,
            'modelLangFormat' => $this->modelLangFormat,
            'resource' => $this->resource,
            'mediaCollections' => $this->mediaCollections,
            'relations' => $this->relations->toLegacyArray(),
            //has
            'hasCreatedByAdminUser' => $hasCreatedByAdminUser,
            'hasUpdatedByAdminUser' => $hasUpdatedByAdminUser,
            'hasTranslatable' => $columns->hasByMajorType('json'),
            'hasPublishedAt' => $columns->hasByName('published_at'),
            'hasPassword' => $leftFormColumns->hasByName('password'),
            'hasEmail' => $leftFormColumns->hasByName('email'),
            'hasWysiwyg' => $leftFormColumns->hasWysiwyg(),
            'hasBoolColumns' => $leftFormColumns->hasByMajorType('bool'),
            'hasDateColumns' => $leftFormColumns->hasByMajorType('date'),
            'hasTimeColumns' => $leftFormColumns->hasByMajorType('time'),
            'hasDatetimeColumns' => $leftFormColumns->hasByMajorType('datetime'),
            'hasFormInput' => $leftFormColumns->hasFormInput(),
            'hasTextarea' => $leftFormColumns->hasTextarea(),
            'hasLocalizedInput' => $leftFormColumns->hasLocalizedInput(),
            'hasLocalizedWysiwyg' => $leftFormColumns->hasLocalizedWysiwyg(),
            'hasForeignKeys' => $foreignKeys->isNotEmpty(),
            //columns
            'columns' => $visibleColumns->toLegacyCollection(),
            'leftFormColumns' => $leftFormColumnsLegacy,
            'leftMediaCollections' => $this->mediaCollections->reject(
                static fn (MediaCollection $mediaCollection): bool => $mediaCollection->collectionName === 'gallery',
            ),
            'rightFormColumns' => $rightFormColumns->toLegacyCollection(),
            'rightMediaCollections' => $rightMediaCollections,
            'foreignKeys' => $foreignKeys,
            'belongsToManyTables' => $this->relations->getBelongsToManyTables(),
            'wysiwygTextColumnNames' => $columns->getWysiwygColumnNames(),

            'isUsedTwoColumnsLayout' => $rightFormColumns->isNotEmpty()
                || $rightMediaCollections->isNotEmpty()
                || $hasCreatedByAdminUser
                || $hasUpdatedByAdminUser,
        ];
    }

    private function buildCreate(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);
        $data = $this->getCommonViewData($columns);

        return view('brackets/admin-generator::' . $this->create, $data)->render();
    }

    private function buildEdit(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);
        $data = $this->getCommonViewData($columns);

        $data['modelLabelColumn'] = $columns->getLabelColumn();

        return view('brackets/admin-generator::' . $this->edit, $data)->render();
    }

    private function buildFormVue(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);
        $leftFormColumns = $columns->getVisible()->rejectByName(
            'published_at',
            'created_by_admin_user_id',
            'updated_by_admin_user_id',
        );
        $data = $this->getCommonViewData($columns);

        $data['validationRules'] = $leftFormColumns->getFrontendValidationRules();

        // Pre-compute media-related strings for the template
        $data['mediaDefaultProp'] = '{' . $this->mediaCollections->keys()
            ->map(static fn (string $key): string => "$key: {}")
            ->implode(', ') . '}';
        $data['mediaCollectionNames'] = $this->mediaCollections->keys()
            ->map(static fn (string $key): string => "'$key'")
            ->implode(', ');

        return view('brackets/admin-generator::' . $this->formVue, $data)->render();
    }

    private function generateCreate(bool $force): void
    {
        $viewPath = resource_path('views/admin/' . $this->modelViewsDirectory . '/create.blade.php');

        if ($this->alreadyExists($viewPath) && !$force) {
            $this->error('File ' . $viewPath . ' already exists!');

            return;
        }

        if ($this->alreadyExists($viewPath) && $force) {
            $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
            $this->files->delete($viewPath);
        }

        $this->makeDirectory($viewPath);

        $this->files->put($viewPath, $this->buildCreate());
        $this->info('Generating ' . $viewPath . ' finished');
    }

    private function generateEdit(bool $force): void
    {
        $viewPath = resource_path('views/admin/' . $this->modelViewsDirectory . '/edit.blade.php');

        if ($this->alreadyExists($viewPath) && !$force) {
            $this->error('File ' . $viewPath . ' already exists!');

            return;
        }

        if ($this->alreadyExists($viewPath) && $force) {
            $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
            $this->files->delete($viewPath);
        }

        $this->makeDirectory($viewPath);

        $this->files->put($viewPath, $this->buildEdit());
        $this->info('Generating ' . $viewPath . ' finished');
    }

    private function generateFormVue(bool $force): void
    {
        $formVuePath = resource_path('js/admin/' . $this->modelJSName . '/Form.vue');

        if ($this->alreadyExists($formVuePath) && !$force) {
            $this->error('File ' . $formVuePath . ' already exists!');

            return;
        }

        if ($this->alreadyExists($formVuePath) && $force) {
            $this->warn('File ' . $formVuePath . ' already exists! File will be deleted.');
            $this->files->delete($formVuePath);
        }

        $this->makeDirectory($formVuePath);

        $this->files->put($formVuePath, $this->buildFormVue());
        $this->info('Generating ' . $formVuePath . ' finished');
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

        $importLine = "import {$this->modelBaseName}Form from './{$this->modelJSName}/Form.vue';";
        $componentLine = "app.component('{$this->modelBaseName}Form', {$this->modelBaseName}Form);";

        if (!str_contains($content, $importLine)) {
            $content = str_replace($importMarker, $importLine . PHP_EOL . $importMarker, $content);
        }

        if (!str_contains($content, $componentLine)) {
            $content = str_replace($componentMarker, $componentLine . PHP_EOL . $componentMarker, $content);
        }

        $this->files->put($adminJsPath, $content);
    }
}
