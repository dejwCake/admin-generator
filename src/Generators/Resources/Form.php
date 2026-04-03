<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Override;
use Symfony\Component\Console\Input\InputOption;

final class Form extends ResourceGenerator
{
    private const array WYSIWYG_COLUMN_NAMES = ['text', 'body', 'description'];

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
        if (in_array($col['name'], $foreignKeys->pluck('column')->toArray(), true)) {
            $fk = $foreignKeys->keyBy('column')[$col['name']];
            $col['isForeignKey'] = true;
            $col['foreignKeyOptionsName'] = $fk['optionsPropName'];
            $col['foreignKeyLabel'] = $fk['foreignKeyLabel'];
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

        if ($belongsToMany !== null) {
            $this->setBelongToManyRelation($belongsToMany);
        }

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

            $foreignKeyLabel = $this->getRelatedLabelColumn($relatedTable, $this->modelVariableName);

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
    private function getCommonViewData(): array
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName)
            ->toLegacyCollection();
        $visibleColumns = $this->getVisibleColumns($this->tableName, $this->modelVariableName);
        $foreignKeys = $this->detectForeignKeys($visibleColumns);

        $hasTranslatable = $columns->contains(
            static fn (array $column): bool => $column['majorType'] === 'json',
        );

        $hasPublishedAt = $columns->contains(
            static fn (array $column): bool => $column['name'] === 'published_at',
        );
        $hasCreatedByAdminUser = $columns->contains(
            static fn (array $column): bool => $column['name'] === 'created_by_admin_user_id',
        );
        $hasUpdatedByAdminUser = $columns->contains(
            static fn (array $column): bool => $column['name'] === 'updated_by_admin_user_id',
        );

        // Right column: only published_at
        $rightFormColumns = $visibleColumns->filter(
            static fn (array $col): bool => $col['name'] === 'published_at',
        );
        // Columns to display in the main form body (excluding sidebar/system columns)
        $leftFormColumns = $visibleColumns->reject(
            static fn (array $col): bool => in_array(
                $col['name'],
                ['published_at', 'created_by_admin_user_id', 'updated_by_admin_user_id'],
                true,
            ),
        )->map(static fn (array $col): array => self::enrichWithForeignKey($col, $foreignKeys));

        // Split media collections: gallery goes right, rest left
        $rightMediaCollections = $this->mediaCollections->filter(
            static fn (object $collection): bool => $collection->collectionName === 'gallery',
        );
        $leftMediaCollections = $this->mediaCollections->reject(
            static fn (object $collection): bool => $collection->collectionName === 'gallery',
        );

        $isUsedTwoColumnsLayout = $rightFormColumns->isNotEmpty()
            || $rightMediaCollections->isNotEmpty()
            || $hasCreatedByAdminUser
            || $hasUpdatedByAdminUser;

        $hasWysiwyg = $leftFormColumns->contains(
            static fn (array $col): bool => ($col['majorType'] === 'text'
                    && in_array($col['name'], self::WYSIWYG_COLUMN_NAMES, true))
                || ($col['majorType'] === 'json'
                    && in_array($col['name'], self::WYSIWYG_COLUMN_NAMES, true)),
        );

        $hasPassword = $leftFormColumns->contains(
            static fn (array $col): bool => $col['name'] === 'password',
        );

        $hasEmail = $leftFormColumns->contains(
            static fn (array $col): bool => $col['name'] === 'email',
        );

        $hasBoolColumns = $leftFormColumns->contains(
            static fn (array $col): bool => $col['majorType'] === 'bool',
        );

        $hasDateColumns = $leftFormColumns->contains(
            static fn (array $col): bool => $col['majorType'] === 'date',
        );

        $hasTimeColumns = $leftFormColumns->contains(
            static fn (array $col): bool => $col['majorType'] === 'time',
        );

        $hasDatetimeColumns = $leftFormColumns->contains(
            static fn (array $col): bool => $col['majorType'] === 'datetime',
        );

        $hasTextarea = $leftFormColumns->contains(
            static fn (array $col): bool => $col['majorType'] === 'text'
                && !in_array($col['name'], self::WYSIWYG_COLUMN_NAMES, true),
        );

        $hasFormInput = $leftFormColumns->contains(
            static fn (array $col): bool => !in_array($col['name'], ['password', 'email'], true)
                && !in_array($col['majorType'], ['json', 'text', 'bool', 'date', 'time', 'datetime'], true)
                && !($col['isForeignKey'] ?? false),
        );

        $hasLocalizedInput = $leftFormColumns->contains(
            static fn (array $col): bool => $col['majorType'] === 'json'
                && !in_array($col['name'], self::WYSIWYG_COLUMN_NAMES, true),
        );

        $hasLocalizedWysiwyg = $leftFormColumns->contains(
            static fn (array $col): bool => $col['majorType'] === 'json'
                && in_array($col['name'], self::WYSIWYG_COLUMN_NAMES, true),
        );

        $belongsToManyTables = (new Collection($this->relations['belongsToMany'] ?? []))
            ->pluck('related_table');

        return [
            'modelBaseName' => $this->modelBaseName,
            'modelPlural' => $this->modelPlural,
            'modelVariableName' => $this->modelVariableName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelDotNotation' => $this->modelDotNotation,
            'modelJSName' => $this->modelJSName,
            'modelLangFormat' => $this->modelLangFormat,
            'resource' => $this->resource,

            'columns' => $visibleColumns,
            'leftFormColumns' => $leftFormColumns,
            'rightFormColumns' => $rightFormColumns,
            'foreignKeys' => $foreignKeys,
            'belongsToManyTables' => $belongsToManyTables,
            'relations' => array_merge(['belongsToMany' => []], $this->relations),
            'mediaCollections' => $this->mediaCollections,
            'leftMediaCollections' => $leftMediaCollections,
            'rightMediaCollections' => $rightMediaCollections,

            'hasTranslatable' => $hasTranslatable,
            'isUsedTwoColumnsLayout' => $isUsedTwoColumnsLayout,
            'hasCreatedByAdminUser' => $hasCreatedByAdminUser,
            'hasUpdatedByAdminUser' => $hasUpdatedByAdminUser,
            'hasWysiwyg' => $hasWysiwyg,
            'hasPassword' => $hasPassword,
            'hasEmail' => $hasEmail,
            'hasBoolColumns' => $hasBoolColumns,
            'hasDateColumns' => $hasDateColumns,
            'hasTimeColumns' => $hasTimeColumns,
            'hasDatetimeColumns' => $hasDatetimeColumns,
            'hasTextarea' => $hasTextarea,
            'hasFormInput' => $hasFormInput,
            'hasLocalizedInput' => $hasLocalizedInput,
            'hasLocalizedWysiwyg' => $hasLocalizedWysiwyg,
            'hasForeignKeys' => $foreignKeys->isNotEmpty(),
            'hasPublishedAt' => $hasPublishedAt,
            'wysiwygTextColumnNames' => self::WYSIWYG_COLUMN_NAMES,
        ];
    }

    private function buildCreate(): string
    {
        $data = $this->getCommonViewData();

        return view('brackets/admin-generator::' . $this->create, $data)->render();
    }

    private function buildEdit(): string
    {
        $data = $this->getCommonViewData();
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName)
            ->toLegacyCollection();

        $data['modelTitle'] = $columns->filter(static fn (array $column): bool => in_array(
            $column['name'],
            ['title', 'name', 'first_name', 'email'],
            true,
        ))->first(null, ['name' => 'id'])['name'];

        return view('brackets/admin-generator::' . $this->edit, $data)->render();
    }

    private function buildFormVue(): string
    {
        $data = $this->getCommonViewData();

        // Build validation schema
        $validationRules = [];
        foreach ($data['leftFormColumns'] as $col) {
            $rules = $this->buildFrontendValidationRules($col);
            if ($rules !== null) {
                $validationRules[$col['name']] = $rules;
            }
        }

        $data['validationRules'] = $validationRules;

        // Pre-compute media-related strings for the template
        $data['mediaDefaultProp'] = '{' . $this->mediaCollections->keys()
            ->map(static fn (string $key): string => "$key: {}")
            ->implode(', ') . '}';
        $data['mediaCollectionNames'] = $this->mediaCollections->keys()
            ->map(static fn (string $key): string => "'$key'")
            ->implode(', ');

        return view('brackets/admin-generator::' . $this->formVue, $data)->render();
    }

    /** @param array<string, string|bool|array<string>> $col */
    private function buildFrontendValidationRules(array $col): ?string
    {
        $rules = [];

        if ($col['name'] === 'password') {
            return null;
        }

        if ($col['name'] === 'email') {
            $rules[] = 'required';
            $rules[] = 'email';

            return "'" . implode('|', $rules) . "'";
        }

        // For FK columns, check if required
        if ($col['isForeignKey'] ?? false) {
            if (in_array('required', $col['frontendRules'] ?? [], true)) {
                return "'required'";
            }

            return null;
        }

        $frontendRules = $col['frontendRules'] ?? [];

        // Filter out rules not suitable for Vue validation
        $filteredRules = array_filter(
            $frontendRules,
            static fn (string $rule): bool => !str_starts_with($rule, 'confirmed:')
                && !str_starts_with($rule, 'date_format:')
                && $rule !== '',
        );

        if ($filteredRules === []) {
            return null;
        }

        return "'" . implode('|', $filteredRules) . "'";
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
