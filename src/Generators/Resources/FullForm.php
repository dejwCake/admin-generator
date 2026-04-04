<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Brackets\AdminGenerator\Dtos\Media\MediaCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Override;
use Symfony\Component\Console\Input\InputOption;

final class FullForm extends ResourceGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:full-form';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate a full-form view template';

    /**
     * Path for blade view
     */
    protected string $view = 'full-form';

    /**
     * Path for Vue form component view
     */
    protected string $formVue = 'form-vue';

    /**
     * Name of view, will be used in directory
     */
    protected string $fileName;

    /**
     * Route to process form
     */
    protected ?string $route;

    protected string $formJsRelativePath;

    //TODO move to ColumnCollectionBuilder
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
        $fileName = $this->option('file-name');
        $this->route = $this->option('route');

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        if ($template !== null) {
            $this->view = 'templates.' . $template . '.full-form';
            $this->formVue = 'templates.' . $template . '.form-vue';
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
                ? 'admin/' . $this->fileName
                : 'admin/' . $this->resource . '/update';
        }

        $this->generateBlade($force);
        $this->generateFormVue($force);
        $this->registerInAdminJs();
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
            'modelJSName' => $this->formJsRelativePath,
            'modelLangFormat' => $this->modelLangFormat,
            'resource' => $this->resource,
            'mediaCollections' => $this->mediaCollections,
            'relations' => $this->relations->toLegacyArray(),
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

            'profileColumns' => $leftFormColumns->rejectByName('password', 'activated', 'forbidden')
                ->toLegacyCollection(),
        ];
    }

    private function buildForm(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);
        $data = $this->getCommonViewData($columns);

        $data['modelLabelColumn'] = $columns->getLabelColumn();

        $data['route'] = $this->route;

        return view('brackets/admin-generator::' . $this->view, $data)->render();
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

        $data['mediaDefaultProp'] = '{' . $this->mediaCollections->keys()
            ->map(static fn (string $key): string => "$key: {}")
            ->implode(', ') . '}';
        $data['mediaCollectionNames'] = $this->mediaCollections->keys()
            ->map(static fn (string $key): string => "'$key'")
            ->implode(', ');

        return view('brackets/admin-generator::' . $this->formVue, $data)->render();
    }

    private function generateBlade(bool $force): void
    {
        $viewPath = resource_path('views/admin/' . $this->fileName . '.blade.php');

        if ($this->alreadyExists($viewPath) && !$force) {
            $this->error('File ' . $viewPath . ' already exists!');

            return;
        }

        if ($this->alreadyExists($viewPath) && $force) {
            $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
            $this->files->delete($viewPath);
        }

        $this->makeDirectory($viewPath);

        $this->files->put($viewPath, $this->buildForm());
        $this->info('Generating ' . $viewPath . ' finished');
    }

    private function generateFormVue(bool $force): void
    {
        $formVuePath = resource_path('js/admin/' . $this->formJsRelativePath . '/Form.vue');

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
