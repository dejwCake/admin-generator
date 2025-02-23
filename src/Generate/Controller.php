<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate;

use Brackets\AdminGenerator\Generate\Traits\FileManipulations;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Controller extends ClassGenerator
{
    use FileManipulations;

    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:controller';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate a controller class';

    /**
     * Path for view
     */
    protected string $view = 'controller';

    /**
     * Controller has also export method
     */
    protected bool $export = false;

    /**
     * Controller has also bulk options method
     */
    protected bool $withoutBulk = false;

    public function handle(): void
    {
        $force = $this->option('force');

        if ($this->option('with-export')) {
            $this->export = true;
        }

        if ($this->option('without-bulk')) {
            $this->withoutBulk = true;
        }
        // TODO test the case, if someone passes a class_name outside Laravel's
        // default App\Http\Controllers folder, if it's going to work

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        $template = $this->option('template');
        if ($template !== null) {
            $this->view = 'templates.' . $template . '.controller';
        }

        $belongsToMany = $this->option('belongs-to-many');
        if ($belongsToMany !== null) {
            $this->setBelongToManyRelation($belongsToMany);
        }

        if ($this->generateClass($force)) {
            $this->info('Generating ' . $this->classFullName . ' finished');

            $icon = Arr::random(
                [
                    'icon-graduation',
                    'icon-puzzle',
                    'icon-compass',
                    'icon-drop',
                    'icon-globe',
                    'icon-ghost',
                    'icon-book-open',
                    'icon-flag',
                    'icon-star',
                    'icon-umbrella',
                    'icon-energy',
                    'icon-plane',
                    'icon-magnet',
                    'icon-diamond',
                ],
            );
            if (
                $this->strReplaceInFile(
                    resource_path('views/admin/layout/sidebar.blade.php'),
                    "{{-- Do not delete me :) I'm used for auto-generation menu items --}}",
                    "<li class=\"nav-item\"><a class=\"nav-link\" href=\"{{ url('admin/" . $this->resource . "') }}\"><i class=\"nav-icon " . $icon . "\"></i> {{ trans('admin." . $this->modelLangFormat . ".title') }}</a></li>" . PHP_EOL . "           {{-- Do not delete me :) I'm used for auto-generation menu items --}}",
                    '|url\(\'admin\/' . $this->resource . '\'\)|',
                )
            ) {
                $this->info('Updating sidebar');
            }
        }
    }

    public function generateClassNameFromTable(string $tableName): string
    {
        return Str::studly($tableName) . 'Controller';
    }

    protected function buildClass(): string
    {
        return view('brackets/admin-generator::' . $this->view, [
            'controllerBaseName' => $this->classBaseName,
            'controllerNamespace' => $this->classNamespace,
            'modelBaseName' => $this->modelBaseName,
            'modelFullName' => $this->modelFullName,
            'modelPlural' => $this->modelPlural,
            'modelVariableName' => $this->modelVariableName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelDotNotation' => $this->modelDotNotation,
            'modelWithNamespaceFromDefault' => $this->modelWithNamespaceFromDefault,
            'export' => $this->export,
            'withoutBulk' => $this->withoutBulk,
            'exportBaseName' => $this->exportBaseName,
            'resource' => $this->resource,
            'containsPublishedAtColumn' => in_array(
                'published_at',
                array_column($this->readColumnsFromTable($this->tableName)->toArray(), 'name'),
                true,
            ),
            // index
            'columnsToQuery' => $this->getColumnsToQuery(),
            'columnsToSearchIn' => $this->readColumnsFromTable($this->tableName)->filter(
                static fn (array $column): bool => (
                    in_array($column['majorType'], ['json', 'text', 'string'], true)
                    || $column['name'] === 'id')
                    && !in_array($column['name'], ['password', 'remember_token'], true),
            )->pluck('name')
            ->toArray(),
            //            'filters' => $this->readColumnsFromTable($tableName)->filter(function($column) {
            //                return in_array($column['majorType'], ['bool', 'date'], true);
            //            }),
            // validation in store/update
            'columns' => $this->getVisibleColumns($this->tableName, $this->modelVariableName),
            'relations' => $this->relations,
            'hasSoftDelete' => $this->readColumnsFromTable($this->tableName)->filter(
                static fn (array $column): bool => $column['name'] === 'deleted_at',
            )->count() > 0,
        ])->render();
    }

    /** @return array<array<string|int>> */
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating controller'],
            ['model-with-full-namespace', 'fnm', InputOption::VALUE_OPTIONAL, 'Specify model with full namespace'],
            ['with-export', 'e', InputOption::VALUE_NONE, 'Generate an option to Export as Excel'],
            ['without-bulk', 'wb', InputOption::VALUE_NONE, 'Generate without bulk options'],
        ];
    }

    /** @return array<array<string|int>> */
    protected function getArguments(): array
    {
        return array_merge(
            parent::getArguments(),
            [
                ['class_name', InputArgument::OPTIONAL, 'Name of the generated class'],
            ],
        );
    }

    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return $rootNamespace . '\Http\Controllers\Admin';
    }

    /** @return array<string> */
    private function getColumnsToQuery(): array
    {
        $columns = $this->readColumnsFromTable($this->tableName);
        $createdByAdminUserIdPresent = $columns->contains('name', 'created_by_admin_user_id');
        $updatedByAdminUserIdPresent = $columns->contains('name', 'updated_by_admin_user_id');

        return $columns
            ->filter(static function (array $column) use ($createdByAdminUserIdPresent, $updatedByAdminUserIdPresent) {
                $haystack = ['password', 'remember_token', 'slug', 'created_at', 'updated_at', 'deleted_at'];
                if ($createdByAdminUserIdPresent && $updatedByAdminUserIdPresent) {
                    $haystack = ['password', 'remember_token', 'slug', 'deleted_at'];
                } else if ($createdByAdminUserIdPresent) {
                    $haystack = ['password', 'remember_token', 'slug', 'updated_at', 'deleted_at'];
                } else if ($updatedByAdminUserIdPresent) {
                    $haystack = ['password', 'remember_token', 'slug', 'created_at', 'deleted_at'];
                }

                return !($column['majorType'] === 'text' || in_array($column['name'], $haystack, true,));
            })->pluck('name')->toArray();
    }
}
