<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Illuminate\Support\Collection;
use Override;
use Symfony\Component\Console\Input\InputOption;

final class VueListing extends ResourceGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:vue-listing';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate a listing Vue component';

    protected string $view = 'vue-listing';

    protected bool $hasExport = false;

    protected bool $hasBulk = true;

    public function handle(): void
    {
        $force = (bool) $this->option('force');
        $template = $this->option('template');

        if ($template !== null) {
            $this->view = 'templates.' . $template . '.vue-listing';
        }

        if ($this->option('with-export')) {
            $this->hasExport = true;
        }

        if ($this->option('without-bulk')) {
            $this->hasBulk = false;
        }

        $this->relations = $this->relationBuilder->build($this->tableName, null);

        $path = resource_path('js/admin/' . $this->modelJSName . '/Listing.vue');

        $this->generate($path, $force);
        $this->registerInAdminJs();
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating listing'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['with-export', 'e', InputOption::VALUE_NONE, 'Generate an option to Export as Excel'],
            ['without-bulk', 'wb', InputOption::VALUE_NONE, 'Generate without bulk options'],
        ];
    }

    private function generate(string $path, bool $force): void
    {
        if ($this->alreadyExists($path) && !$force) {
            $this->error('File ' . $path . ' already exists!');

            return;
        }

        if ($this->alreadyExists($path) && $force) {
            $this->warn('File ' . $path . ' already exists! File will be deleted.');
            $this->files->delete($path);
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->build());
        $this->info('Generating ' . $path . ' finished');
    }

    private function build(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName)
            ->getForIndex();

        $hasPublishedAt = $columns->hasByName('published_at');
        $hasUserDetailTooltip = $columns->hasByName('created_by_admin_user_id')
            || $columns->hasByName('updated_by_admin_user_id');

        $hasDateColumns = $columns->hasByMajorType('date', 'time', 'datetime')
            || $hasPublishedAt
            || $hasUserDetailTooltip;

        $dateImports = new Collection();
        if ($columns->hasByMajorType('date')) {
            $dateImports->push('formatDate');
        }
        if ($columns->hasByMajorType('time')) {
            $dateImports->push('formatTime');
        }
        if ($columns->hasByMajorType('datetime') || $hasPublishedAt || $hasUserDetailTooltip) {
            $dateImports->push('formatDatetime');
        }
        $dateImports = $dateImports->sort();

        return view('brackets/admin-generator::' . $this->view, [
            'modelJSName' => $this->modelJSName,
            'modelVariableName' => $this->modelVariableName,
            'relations' => $this->relations,
            'hasExport' => $this->hasExport,
            'hasBulk' => $this->hasBulk,
            'hasPublishedAt' => $hasPublishedAt,
            'hasUserDetailTooltip' => $hasUserDetailTooltip,
            'hasSwitchColumns' => $columns->hasByMajorType('bool'),
            'hasDateColumns' => $hasDateColumns,
            'columns' => $columns,
            'dateImports' => $dateImports,
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
