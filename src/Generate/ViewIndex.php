<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate;

use Symfony\Component\Console\Input\InputOption;

class ViewIndex extends ViewGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:index';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate an index view template';

    /**
     * Path for view
     */
    protected string $view = 'index';

    /**
     * Path for js view
     */
    protected string $viewJs = 'listing-js';

    /**
     * Index view has also export button
     */
    protected bool $export = false;

    /**
     * Index view has also bulk options
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

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        if (!empty($template = $this->option('template'))) {
            $this->view = 'templates.' . $template . '.index';
            $this->viewJs = 'templates.' . $template . '.listing-js';
        }

        $viewPath = resource_path('views/admin/' . $this->modelViewsDirectory . '/index.blade.php');
        $listingJsPath = resource_path('js/admin/' . $this->modelJSName . '/Listing.js');
        $indexJsPath = resource_path('js/admin/' . $this->modelJSName . '/index.js');
        $bootstrapJsPath = resource_path('js/admin/index.js');

        if ($this->alreadyExists($viewPath) && !$force) {
            $this->error('File ' . $viewPath . ' already exists!');
        } else {
            if ($this->alreadyExists($viewPath) && $force) {
                $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
                $this->files->delete($viewPath);
            }

            $this->makeDirectory($viewPath);

            $this->files->put($viewPath, $this->buildView());

            $this->info('Generating ' . $viewPath . ' finished');
        }


        if ($this->alreadyExists($listingJsPath) && !$force) {
            $this->error('File ' . $listingJsPath . ' already exists!');
        } else {
            if ($this->alreadyExists($listingJsPath) && $force) {
                $this->warn('File ' . $listingJsPath . ' already exists! File will be deleted.');
                $this->files->delete($listingJsPath);
            }

            $this->makeDirectory($listingJsPath);

            $this->files->put($listingJsPath, $this->buildListingJs());
            $this->info('Generating ' . $listingJsPath . ' finished');
        }


        if ($this->appendIfNotAlreadyAppended($indexJsPath, "import './Listing';" . PHP_EOL)) {
            $this->info('Appending Listing to ' . $indexJsPath . ' finished');
        }
        if ($this->appendIfNotAlreadyAppended($bootstrapJsPath, "import './" . $this->modelJSName . "';" . PHP_EOL)) {
            $this->info('Appending ' . $this->modelJSName . '/index.js to ' . $bootstrapJsPath . ' finished');
        }
    }

    protected function buildView(): string
    {
        return view('brackets/admin-generator::' . $this->view, [
            'modelBaseName' => $this->modelBaseName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelPlural' => $this->modelPlural,
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelJSName' => $this->modelJSName,
            'modelDotNotation' => $this->modelDotNotation,
            'modelLangFormat' => $this->modelLangFormat,
            'resource' => $this->resource,
            'export' => $this->export,
            'containsPublishedAtColumn' => in_array(
                "published_at",
                array_column($this->readColumnsFromTable($this->tableName)->toArray(), 'name'),
            ),
            'withoutBulk' => $this->withoutBulk,

            'columns' => $this->readColumnsFromTable(
                $this->tableName,
            )->reject(static fn ($column) => $column['type'] === 'text'
                        || in_array(
                            $column['name'],
                            ["password", "remember_token", "slug", "created_at", "updated_at", "deleted_at"],
                        )
                        || ($column['type'] === 'json' && in_array(
                            $column['name'],
                            ["perex", "text", "body"],
                        )))->map(
                            static function ($col) {

                                $filters = collect([]);
                                $col['switch'] = false;

                                if ($col['type'] === 'date' || $col['type'] === 'time' || $col['type'] === 'datetime') {
                                    $filters->push($col['type']);
                                }

                                if (
                                    $col['type'] === 'boolean'
                                    && (
                                    $col['name'] === 'enabled'
                                    || $col['name'] === 'activated'
                                    || $col['name'] === 'is_published'
                                    )
                                ) {
                                    $col['switch'] = true;
                                }

                                $col['filters'] = $filters->isNotEmpty()

                                    ? ' | ' . implode(
                                        ' | ',
                                        $filters->toArray(),
                                    )

                                    : '';

                                return $col;
                            },
                        ),
//            'filters' => $this->readColumnsFromTable($tableName)->filter(function($column) {
//                return $column['type'] == 'boolean' || $column['type'] == 'date';
//            }),
        ])->render();
    }

    protected function buildListingJs(): string
    {
        return view('brackets/admin-generator::' . $this->viewJs, [
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelJSName' => $this->modelJSName,
        ])->render();
    }

    /** @return array<array<string|int>> */
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating index'],
            ['with-export', 'e', InputOption::VALUE_NONE, 'Generate an option to Export as Excel'],
            ['without-bulk', 'wb', InputOption::VALUE_NONE, 'Generate without bulk options'],
        ];
    }
}
