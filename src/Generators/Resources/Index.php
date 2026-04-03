<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Illuminate\Support\Collection;
use Override;
use Symfony\Component\Console\Input\InputOption;

final class Index extends ResourceGenerator
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
     * Path for Vue listing component view
     */
    protected string $viewVue = 'listing-vue';

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
        $force = (bool) $this->option('force');
        $template = $this->option('template');
        $withExport = (bool) $this->option('with-export');
        $withoutBulk = $this->option('without-bulk');

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        if ($template !== null) {
            $this->view = 'templates.' . $template . '.index';
            $this->viewVue = 'templates.' . $template . '.listing-vue';
        }

        if ($withExport) {
            $this->export = true;
        }

        if ($withoutBulk) {
            $this->withoutBulk = true;
        }

        $viewPath = resource_path('views/admin/' . $this->modelViewsDirectory . '/index.blade.php');
        $listingVuePath = resource_path('js/admin/' . $this->modelJSName . '/Listing.vue');

        $this->generateView($viewPath, $force);
        $this->generateListingVue($listingVuePath, $force);
        $this->registerInAdminJs();
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating index'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['with-export', 'e', InputOption::VALUE_NONE, 'Generate an option to Export as Excel'],
            ['without-bulk', 'wb', InputOption::VALUE_NONE, 'Generate without bulk options'],
        ];
    }

    private function generateView(string $viewPath, bool $force): void
    {
        if ($this->alreadyExists($viewPath) && !$force) {
            $this->error('File ' . $viewPath . ' already exists!');

            return;
        }

        if ($this->alreadyExists($viewPath) && $force) {
            $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
            $this->files->delete($viewPath);
        }

        $this->makeDirectory($viewPath);

        $this->files->put($viewPath, $this->buildView());
        $this->info('Generating ' . $viewPath . ' finished');
    }

    private function generateListingVue(string $listingVuePath, bool $force): void
    {
        if ($this->alreadyExists($listingVuePath) && !$force) {
            $this->error('File ' . $listingVuePath . ' already exists!');

            return;
        }

        if ($this->alreadyExists($listingVuePath) && $force) {
            $this->warn('File ' . $listingVuePath . ' already exists! File will be deleted.');
            $this->files->delete($listingVuePath);
        }

        $this->makeDirectory($listingVuePath);

        $this->files->put($listingVuePath, $this->buildListingVue());
        $this->info('Generating ' . $listingVuePath . ' finished');
    }

    private function buildView(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);
        $indexColumns = $columns->getForIndex();
        $indexColumnsWithPriority = $this->getColumnsWithPriority($indexColumns);

        return view('brackets/admin-generator::' . $this->view, [
            //globals
            'modelBaseName' => $this->modelBaseName,
            'modelPlural' => $this->modelPlural,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelJSName' => $this->modelJSName,
            'modelDotNotation' => $this->modelDotNotation,
            'modelLangFormat' => $this->modelLangFormat,
            'export' => $this->export,
            'withoutBulk' => $this->withoutBulk,
            'resource' => $this->resource,
            //has
            'hasPublishedAt' => $columns->hasByName('published_at'),
            'hasCreatedByAdminUser' => $columns->hasByName('created_by_admin_user_id'),
            'hasUpdatedByAdminUser' => $columns->hasByName('updated_by_admin_user_id'),
            //columns
            'columns' => $indexColumnsWithPriority,
        ])->render();
    }

    private function buildListingVue(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);
        $indexColumns = $columns->getForIndex();
        $indexColumnsWithPriority = $this->getColumnsWithPriority($indexColumns);

        $hasPublishedAt = $columns->hasByName('published_at');
        $hasUserDetailTooltip = $columns->hasByName('created_by_admin_user_id')
            || $columns->hasByName('updated_by_admin_user_id');

        $hasDateColumns = $indexColumns->hasByMajorType('date', 'time', 'datetime')
            || $hasPublishedAt
            || $hasUserDetailTooltip;

        $dateImports = new Collection();
        if ($indexColumns->hasByMajorType('date')) {
            $dateImports->push('formatDate');
        }
        if ($indexColumns->hasByMajorType('time')) {
            $dateImports->push('formatTime');
        }
        if ($indexColumns->hasByMajorType('datetime') || $hasPublishedAt || $hasUserDetailTooltip) {
            $dateImports->push('formatDatetime');
        }
        $dateImports = $dateImports->sort();

        return view('brackets/admin-generator::' . $this->viewVue, [
            //globasl
            'modelJSName' => $this->modelJSName,
            'modelVariableName' => $this->modelVariableName,
            'export' => $this->export,
            'withoutBulk' => $this->withoutBulk,
            //has
            'hasPublishedAt' => $hasPublishedAt,
            'hasUserDetailTooltip' => $hasUserDetailTooltip,
            'hasSwitchColumns' => $indexColumns->hasByMajorType('bool'),
            'hasDateColumns' => $hasDateColumns,
            //columns
            'columns' => $indexColumnsWithPriority,
            'dateImports' => $dateImports->implode(', '),
        ])->render();
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

        $importLine = "import {$this->modelBaseName}Listing from './{$this->modelJSName}/Listing.vue';";
        $componentLine = "app.component('{$this->modelBaseName}Listing', {$this->modelBaseName}Listing);";

        if (!str_contains($content, $importLine)) {
            $content = str_replace($importMarker, $importLine . PHP_EOL . $importMarker, $content);
        }

        if (!str_contains($content, $componentLine)) {
            $content = str_replace($componentMarker, $componentLine . PHP_EOL . $componentMarker, $content);
        }

        $this->files->put($adminJsPath, $content);
    }

    //TODO move to ColumnCollectionBuilder
    private function getColumnsWithPriority(ColumnCollection $columns): Collection
    {
        $columnsForIndex = $columns
            ->toLegacyCollection()
            ->map(function (array $column): array {
                $column['priority'] = $this->getColumnFixedPriority($column['name']);

                return $column;
            });

        return $this->assignColumnPriorities($columnsForIndex);
    }

    //TODO move to ColumnCollectionBuilder
    private function getColumnFixedPriority(string $name): ?int
    {
        return match (true) {
            in_array($name, ['name', 'title', 'last_name', 'subject'], true) => 0,
            in_array($name, ['first_name', 'email', 'author'], true) => 1,
            $name === 'id' => 2,
            $name === 'published_at' => 3,
            default => null,
        };
    }

    //TODO move to ColumnCollectionBuilder
    private function assignColumnPriorities(Collection $columns): Collection
    {
        $fixedPriorities = $columns
            ->pluck('priority')
            ->filter(static fn (?int $priority): bool => $priority !== null)
            ->unique()
            ->sort()
            ->values();

        $priorityMap = $fixedPriorities
            ->mapWithKeys(static fn (int $priority, int $index): array => [$priority => $index])
            ->all();

        $nextPriority = count($priorityMap);

        $result = [];

        foreach ($columns as $column) {
            if ($column['priority'] !== null) {
                $column['priority'] = $priorityMap[$column['priority']];
            } else {
                $column['priority'] = min($nextPriority, 10);
                $nextPriority++;
            }

            $result[] = $column;
        }

        return new Collection($result);
    }
}
