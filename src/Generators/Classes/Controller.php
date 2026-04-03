<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Classes;

use Brackets\AdminGenerator\Generators\Traits\FileManipulations;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Override;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class Controller extends ClassGenerator
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
        $template = $this->option('template');
        $belongsToMany = $this->option('belongs-to-many');
        $withExport = $this->option('with-export');
        $withoutBulk = $this->option('without-bulk');
        $media = $this->option('media');

        // TODO test the case, if someone passes a class_name outside Laravel's
        // default App\Http\Controllers folder, if it's going to work

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        if ($template !== null) {
            $this->view = 'templates.' . $template . '.controller';
        }

        if ($belongsToMany !== null) {
            $this->setBelongToManyRelation($belongsToMany);
        }

        if ($withExport) {
            $this->export = true;
        }

        if ($withoutBulk) {
            $this->withoutBulk = true;
        }

        if ($media !== null && $media !== []) {
            $this->mediaCollections = $this->mediaCollectionBuilder->build($media);
        }

        if ($this->generateClass($force)) {
            $this->info('Generating ' . $this->classFullName . ' finished');

            $this->addToSidebar();
        }
    }

    #[Override]
    public function generateClassNameFromTable(string $tableName): string
    {
        return Str::studly($tableName) . 'Controller';
    }

    #[Override]
    protected function buildClass(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName)
            ->toLegacyCollection();

        return view('brackets/admin-generator::' . $this->view, [
            'controllerBaseName' => $this->classBaseName,
            'controllerNamespace' => $this->classNamespace,
            'exportBaseName' => $this->exportBaseName,
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
            'resource' => $this->resource,
            // index
            'columnsToQuery' => $this->getColumnsToQuery($columns),
            'columnsToSearchIn' => $columns->filter(
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
            'mediaCollections' => $this->mediaCollections,
            'hasPublishedAt' => $columns->contains(
                static fn (array $column): bool => $column['name'] === 'published_at',
            ),
        ])->render();
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['model-with-full-namespace', 'fnm', InputOption::VALUE_OPTIONAL, 'Specify model with full namespace'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating controller'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
            ['with-export', 'e', InputOption::VALUE_NONE, 'Generate an option to Export as Excel'],
            ['without-bulk', 'wb', InputOption::VALUE_NONE, 'Generate without bulk options'],
            ['media', 'M', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Media collections (format: name:type:disk:maxFiles)'],
        ];
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getArguments(): array
    {
        return array_merge(
            parent::getArguments(),
            [
                ['class_name', InputArgument::OPTIONAL, 'Name of the generated class'],
            ],
        );
    }

    #[Override]
    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return $rootNamespace . '\Http\Controllers\Admin';
    }

    private function addToSidebar(): void
    {
        $icon = Arr::random(
            [
                'fa fa-graduation-cap',
                'fa fa-puzzle-piece',
                'fa fa-compass',
                'fa fa-droplet',
                'fa fa-globe',
                'fa fa-ghost',
                'fa fa-book-open',
                'fa fa-flag',
                'fa fa-star',
                'fa fa-umbrella',
                'fa fa-bolt',
                'fa fa-plane',
                'fa fa-magnet',
                'fa fa-gem',
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

    /** @return array<string> */
    private function getColumnsToQuery(Collection $columns): array
    {
        $createdByAdminUserIdPresent = $columns->contains('name', 'created_by_admin_user_id');
        $updatedByAdminUserIdPresent = $columns->contains('name', 'updated_by_admin_user_id');

        return $columns
            ->filter(static function (array $column) use ($createdByAdminUserIdPresent, $updatedByAdminUserIdPresent) {
                $haystack = ['password', 'remember_token', 'slug', 'created_at', 'updated_at', 'deleted_at'];
                if ($createdByAdminUserIdPresent && $updatedByAdminUserIdPresent) {
                    $haystack = ['password', 'remember_token', 'slug', 'deleted_at'];
                } elseif ($createdByAdminUserIdPresent) {
                    $haystack = ['password', 'remember_token', 'slug', 'updated_at', 'deleted_at'];
                } elseif ($updatedByAdminUserIdPresent) {
                    $haystack = ['password', 'remember_token', 'slug', 'created_at', 'deleted_at'];
                }

                return !($column['majorType'] === 'text' || in_array($column['name'], $haystack, true));
            })->pluck('name')->toArray();
    }
}
