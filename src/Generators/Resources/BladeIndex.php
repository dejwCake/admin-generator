<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Override;
use Symfony\Component\Console\Input\InputOption;

final class BladeIndex extends ResourceGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:blade-index';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate an index blade view';

    protected string $view = 'blade-index';

    protected bool $hasExport = false;

    protected bool $hasBulk = true;

    public function handle(): void
    {
        $force = (bool) $this->option('force');
        $template = $this->option('template');

        if ($template !== null) {
            $this->view = 'templates.' . $template . '.blade-index';
        }

        if ($this->option('with-export')) {
            $this->hasExport = true;
        }

        if ($this->option('without-bulk')) {
            $this->hasBulk = false;
        }

        $path = resource_path('views/admin/' . $this->modelViewsDirectory . '/index.blade.php');

        $this->generate($path, $force);
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

    #[Override]
    protected function buildView(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName)
            ->getForIndex();

        return view('brackets/admin-generator::' . $this->view, [
            'modelBaseName' => $this->modelBaseName,
            'modelPlural' => $this->modelPlural,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelJSName' => $this->modelJSName,
            'modelDotNotation' => $this->modelDotNotation,
            'modelLangFormat' => $this->modelLangFormat,
            'resource' => $this->resource,
            'hasExport' => $this->hasExport,
            'hasBulk' => $this->hasBulk,
            'hasPublishedAt' => $columns->hasByName('published_at'),
            'hasCreatedByAdminUser' => $columns->hasByName('created_by_admin_user_id'),
            'hasUpdatedByAdminUser' => $columns->hasByName('updated_by_admin_user_id'),
            'columns' => $columns,
        ])->render();
    }
}
