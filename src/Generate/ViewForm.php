<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate;

use Symfony\Component\Console\Input\InputOption;

class ViewForm extends ViewGenerator
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
     * Path for form view
     */
    protected string $form = 'form';

    /**
     * Path for form right view
     */
    protected string $formRight = 'form-right';

    /**
     * Path for js view
     */
    protected string $formJs = 'form-js';

    public function handle(): void
    {
        $force = (bool) $this->option('force');

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        $template = $this->option('template');
        if ($template !== null) {
            $this->create = 'templates.' . $template . '.create';
            $this->edit = 'templates.' . $template . '.edit';
            $this->form = 'templates.' . $template . '.form';
            $this->formRight = 'templates.' . $template . 'form-right';
            $this->formJs = 'templates.' . $template . '.form-js';
        }

        $belongsToMany = $this->option('belongs-to-many');
        if ($belongsToMany !== null) {
            $this->setBelongToManyRelation($belongsToMany);
        }

        $this->generateForm($force);
        $this->generateRightForm($force);
        $this->generateCreate($force);
        $this->generateEdit($force);
        $this->generateFormJs($force);

        $indexJsPath = resource_path('js/admin/' . $this->modelJSName . '/index.js');
        $bootstrapJsPath = resource_path('js/admin/index.js');

        if ($this->appendIfNotAlreadyAppended($indexJsPath, 'import \'./Form\';' . PHP_EOL)) {
            $this->info('Appending Form to ' . $indexJsPath . ' finished');
        }
        if ($this->appendIfNotAlreadyAppended($bootstrapJsPath, 'import \'./' . $this->modelJSName . '\';' . PHP_EOL)) {
            $this->info('Appending Form to ' . $bootstrapJsPath . ' finished');
        }
    }

    protected function isUsedTwoColumnsLayout(): bool
    {
        return in_array(
            'published_at',
            array_column($this->readColumnsFromTable($this->tableName)->toArray(), 'name'),
            true,
        );
    }

    protected function buildForm(): string
    {
        return view('brackets/admin-generator::' . $this->form, [
            'modelBaseName' => $this->modelBaseName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelPlural' => $this->modelPlural,
            'modelDotNotation' => $this->modelDotNotation,
            'modelLangFormat' => $this->modelLangFormat,

            'columns' => $this->getVisibleColumns($this->tableName, $this->modelVariableName)
                ->sortBy(static fn (array $column): bool => !($column['majorType'] === 'json')),
            'hasTranslatable' => $this->readColumnsFromTable($this->tableName)
                    ->filter(static fn (array $column): bool => $column['majorType'] === 'json')
                    ->count() > 0,
            'wysiwygTextColumnNames' => ['text', 'body', 'description'],
            'relations' => $this->relations,
        ])->render();
    }

    protected function buildFormRight(): string
    {
        return view('brackets/admin-generator::' . $this->formRight, [
            'modelBaseName' => $this->modelBaseName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelPlural' => $this->modelPlural,
            'modelDotNotation' => $this->modelDotNotation,
            'modelLangFormat' => $this->modelLangFormat,
            'modelVariableName' => $this->modelVariableName,

            'columns' => $this->getVisibleColumns($this->tableName, $this->modelVariableName)
                ->sortBy(static fn (array $column): bool => !($column['majorType'] === 'json')),
            'hasTranslatable' => $this->readColumnsFromTable($this->tableName)
                    ->filter(static fn (array $column): bool => $column['majorType'] === 'json')
                    ->count() > 0,
            'translatableTextarea' => ['perex', 'text', 'body'],
            'relations' => $this->relations,
        ])->render();
    }

    protected function buildCreate(): string
    {
        return view('brackets/admin-generator::' . $this->create, [
            'modelBaseName' => $this->modelBaseName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelVariableName' => $this->modelVariableName,
            'modelPlural' => $this->modelPlural,
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelDotNotation' => $this->modelDotNotation,
            'modelJSName' => $this->modelJSName,
            'modelLangFormat' => $this->modelLangFormat,
            'resource' => $this->resource,
            'isUsedTwoColumnsLayout' => $this->isUsedTwoColumnsLayout(),

            'columns' => $this->getVisibleColumns($this->tableName, $this->modelVariableName),
            'hasTranslatable' => $this->readColumnsFromTable($this->tableName)
                    ->filter(static fn (array $column): bool => $column['majorType'] === 'json')
                    ->count() > 0,
        ])->render();
    }

    protected function buildEdit(): string
    {
        return view('brackets/admin-generator::' . $this->edit, [
            'modelBaseName' => $this->modelBaseName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelVariableName' => $this->modelVariableName,
            'modelPlural' => $this->modelPlural,
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelDotNotation' => $this->modelDotNotation,
            'modelJSName' => $this->modelJSName,
            'modelLangFormat' => $this->modelLangFormat,
            'resource' => $this->resource,
            'isUsedTwoColumnsLayout' => $this->isUsedTwoColumnsLayout(),

            'modelTitle' => $this->readColumnsFromTable($this->tableName)
                ->filter(static fn (array $column): bool => in_array(
                    $column['name'],
                    ['title', 'name', 'first_name', 'email'],
                    true,
                ))->first(null, ['name' => 'id'])['name'],
            'columns' => $this->getVisibleColumns($this->tableName, $this->modelVariableName),
            'hasTranslatable' => $this->readColumnsFromTable($this->tableName)
                    ->filter(static fn (array $column): bool => $column['majorType'] === 'json')
                    ->count() > 0,
        ])->render();
    }

    protected function buildFormJs(): string
    {
        return view('brackets/admin-generator::' . $this->formJs, [
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelJSName' => $this->modelJSName,

            'columns' => $this->getVisibleColumns($this->tableName, $this->modelVariableName),
        ])->render();
    }

    /** @return array<array<string|int>> */
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating form'],
        ];
    }

    private function generateForm(bool $force): void
    {
        $viewPath = resource_path('views/admin/' . $this->modelViewsDirectory . '/components/form-elements.blade.php');
        if ($this->alreadyExists($viewPath) && !$force) {
            $this->error('File ' . $viewPath . ' already exists!');
        } else {
            if ($this->alreadyExists($viewPath) && $force) {
                $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
                $this->files->delete($viewPath);
            }

            $this->makeDirectory($viewPath);

            $this->files->put($viewPath, $this->buildForm());

            $this->info('Generating ' . $viewPath . ' finished');
        }
    }

    private function generateRightForm(bool $force): void
    {
        if (
            in_array(
                'published_at',
                array_column(
                    $this->getVisibleColumns($this->tableName, $this->modelVariableName)->toArray(),
                    'name',
                ),
                true,
            )
        ) {
            $viewPath = resource_path(
                'views/admin/' . $this->modelViewsDirectory . '/components/form-elements-right.blade.php',
            );
            if ($this->alreadyExists($viewPath) && !$force) {
                $this->error('File ' . $viewPath . ' already exists!');
            } else {
                if ($this->alreadyExists($viewPath) && $force) {
                    $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
                    $this->files->delete($viewPath);
                }

                $this->makeDirectory($viewPath);

                $this->files->put($viewPath, $this->buildFormRight());

                $this->info('Generating ' . $viewPath . ' finished');
            }
        }
    }

    private function generateCreate(bool $force): void
    {
        $viewPath = resource_path('views/admin/' . $this->modelViewsDirectory . '/create.blade.php');
        if ($this->alreadyExists($viewPath) && !$force) {
            $this->error('File ' . $viewPath . ' already exists!');
        } else {
            if ($this->alreadyExists($viewPath) && $force) {
                $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
                $this->files->delete($viewPath);
            }

            $this->makeDirectory($viewPath);

            $this->files->put($viewPath, $this->buildCreate());

            $this->info('Generating ' . $viewPath . ' finished');
        }
    }

    private function generateEdit(bool $force): void
    {
        $viewPath = resource_path('views/admin/' . $this->modelViewsDirectory . '/edit.blade.php');
        if ($this->alreadyExists($viewPath) && !$force) {
            $this->error('File ' . $viewPath . ' already exists!');
        } else {
            if ($this->alreadyExists($viewPath) && $force) {
                $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
                $this->files->delete($viewPath);
            }

            $this->makeDirectory($viewPath);

            $this->files->put($viewPath, $this->buildEdit());

            $this->info('Generating ' . $viewPath . ' finished');
        }
    }

    private function generateFormJs(bool $force): void
    {
        $formJsPath = resource_path('js/admin/' . $this->modelJSName . '/Form.js');

        if ($this->alreadyExists($formJsPath) && !$force) {
            $this->error('File ' . $formJsPath . ' already exists!');
        } else {
            if ($this->alreadyExists($formJsPath) && $force) {
                $this->warn('File ' . $formJsPath . ' already exists! File will be deleted.');
                $this->files->delete($formJsPath);
            }

            $this->makeDirectory($formJsPath);

            $this->files->put($formJsPath, $this->buildFormJs());
            $this->info('Generating ' . $formJsPath . ' finished');
        }
    }
}
