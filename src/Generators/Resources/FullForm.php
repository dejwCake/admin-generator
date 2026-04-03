<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Override;
use Symfony\Component\Console\Input\InputOption;

final class FullForm extends ResourceGenerator
{
    private const array WYSIWYG_COLUMN_NAMES = ['text', 'body', 'description'];

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

        if ($belongsToMany !== null) {
            $this->setBelongToManyRelation($belongsToMany);
        }

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

        $rightFormColumns = $visibleColumns->filter(
            static fn (array $col): bool => $col['name'] === 'published_at',
        );
        $leftFormColumns = $visibleColumns->reject(
            static fn (array $col): bool => in_array(
                $col['name'],
                ['published_at', 'created_by_admin_user_id', 'updated_by_admin_user_id'],
                true,
            ),
        )->map(static fn (array $col): array => self::enrichWithForeignKey($col, $foreignKeys));

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

        $hasLanguage = $leftFormColumns->contains(
            static fn (array $col): bool => $col['name'] === 'language',
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
            'modelJSName' => $this->formJsRelativePath,
            'modelLangFormat' => $this->modelLangFormat,
            'resource' => $this->resource,

            'columns' => $visibleColumns,
            'leftFormColumns' => $leftFormColumns,
            'profileColumns' => $leftFormColumns->reject(
                static fn (array $col): bool => in_array($col['name'], ['password', 'activated', 'forbidden'], true),
            ),
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
            'hasLanguage' => $hasLanguage,
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

    /** @param array<string, string|bool|array<string>> $col */
    private function buildFrontendValidationRules(array $col): ?string
    {
        if ($col['name'] === 'password') {
            return null;
        }

        if ($col['name'] === 'email') {
            return "'required|email'";
        }

        if ($col['isForeignKey'] ?? false) {
            if (in_array('required', $col['frontendRules'] ?? [], true)) {
                return "'required'";
            }

            return null;
        }

        $frontendRules = $col['frontendRules'] ?? [];

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

    private function buildForm(): string
    {
        $data = $this->getCommonViewData();
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName)
            ->toLegacyCollection();

        $data['modelTitle'] = $columns->filter(static fn (array $column): bool => in_array(
            $column['name'],
            ['title', 'name', 'first_name', 'email'],
            true,
        ))->first(null, ['name' => 'id'])['name'];

        $data['route'] = $this->route;

        return view('brackets/admin-generator::' . $this->view, $data)->render();
    }

    private function buildFormVue(): string
    {
        $data = $this->getCommonViewData();

        $validationRules = [];
        foreach ($data['leftFormColumns'] as $col) {
            $rules = $this->buildFrontendValidationRules($col);
            if ($rules !== null) {
                $validationRules[$col['name']] = $rules;
            }
        }

        $data['validationRules'] = $validationRules;

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
