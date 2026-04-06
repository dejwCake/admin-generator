<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

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

        $this->relations = $this->relationBuilder->build($this->tableName, null);

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
        $indexColumns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName)
            ->getForIndex();

        return view('brackets/admin-generator::' . $this->view, [
            //globals
            'modelBaseName' => $this->modelBaseName,
            'modelPlural' => $this->modelPlural,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelJSName' => $this->modelJSName,
            'modelDotNotation' => $this->modelDotNotation,
            'modelLangFormat' => $this->modelLangFormat,
            'resource' => $this->resource,
            //has
            'hasExport' => $this->export,
            'hasBulk' => !$this->withoutBulk,
            'hasPublishedAt' => $indexColumns->hasByName('published_at'),
            'hasCreatedByAdminUser' => $indexColumns->hasByName('created_by_admin_user_id'),
            'hasUpdatedByAdminUser' => $indexColumns->hasByName('updated_by_admin_user_id'),
            //columns
            'columns' => $indexColumns->toLegacyCollection(),
        ])->render();
    }

    private function buildListingVue(): string
    {
        $indexColumns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName)
            ->getForIndex();

        $hasPublishedAt = $indexColumns->hasByName('published_at');
        $hasUserDetailTooltip = $indexColumns->hasByName('created_by_admin_user_id')
            || $indexColumns->hasByName('updated_by_admin_user_id');

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
            //globals
            'modelJSName' => $this->modelJSName,
            'modelVariableName' => $this->modelVariableName,
            'relations' => $this->relations,
            //has
            'hasExport' => $this->export,
            'hasBulk' => !$this->withoutBulk,
            'hasPublishedAt' => $hasPublishedAt,
            'hasUserDetailTooltip' => $hasUserDetailTooltip,
            'hasSwitchColumns' => $indexColumns->hasByMajorType('bool'),
            'hasDateColumns' => $hasDateColumns,
            //columns
            'columns' => $indexColumns->toLegacyCollection(),
            'dateImports' => $dateImports->implode(', '),
            //relations
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
}
