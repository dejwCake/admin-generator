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
    protected string $view = 'classes.controller';

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
            $this->view = sprintf('classes.templates.%s.controller', $template);
        }

        $this->relations = $this->relationBuilder->build($this->tableName, $belongsToMany);

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
            $this->info(sprintf('Generating %s finished', $this->classFullName));

            $this->addToSidebar();
        }
    }

    #[Override]
    public function generateClassNameFromTable(string $tableName): string
    {
        return sprintf('%sController', Str::studly($tableName));
    }

    #[Override]
    protected function buildClass(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);
        $visibleColumns = $columns->getVisible();

        $indexEagerLoads = new Collection();
        $editEagerLoads = new Collection();

        if ($visibleColumns->hasByName('created_by_admin_user_id')) {
            $indexEagerLoads->push('createdByAdminUser');
            $editEagerLoads->push('createdByAdminUser');
        }
        if ($visibleColumns->hasByName('updated_by_admin_user_id')) {
            $indexEagerLoads->push('updatedByAdminUser');
            $editEagerLoads->push('updatedByAdminUser');
        }
        foreach ($this->relations->getBelongsTo() as $belongsTo) {
            $indexEagerLoads->push($belongsTo->relationMethodName);
            $editEagerLoads->push($belongsTo->relationMethodName);
        }
        foreach ($this->relations->getBelongsToMany() as $belongsToMany) {
            $editEagerLoads->push($belongsToMany->relationMethodName);
        }

        return $this->viewFactory->make(sprintf('brackets/admin-generator::%s', $this->view), [
            //globals
            'controllerBaseName' => $this->classBaseName,
            'controllerNamespace' => $this->classNamespace,
            'exportBaseName' => $this->exportBaseName,
            'modelBaseName' => $this->modelBaseName,
            'modelFullName' => $this->modelFullName,
            'modelVariableName' => $this->modelVariableName,
            'modelDotNotation' => $this->modelDotNotation,
            'modelWithNamespaceFromDefault' => $this->modelWithNamespaceFromDefault,
            'resource' => $this->resource,
            'relations' => $this->relations,
            //has
            'hasActivation' => $visibleColumns->hasByName('activated'),
            'hasExport' => $this->export,
            'hasBulk' => !$this->withoutBulk,
            'hasPublishedAt' => $columns->hasByName('published_at'),
            //columns
            'queryColumns' => $columns->getToQuery(),
            'searchInColumns' => $columns->getToSearchIn(),
            'visibleColumns' => $visibleColumns,
            //media
            'mediaCollections' => $this->mediaCollections,
            //eager loads
            'indexEagerLoads' => $indexEagerLoads,
            'editEagerLoads' => $editEagerLoads,
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
            [
                'media',
                'M',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Media collections (format: name:type:disk:maxFiles)',
            ],
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
        return sprintf('%s\Http\Controllers\Admin', $rootNamespace);
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
                $this->laravel->resourcePath('views/admin/layout/sidebar.blade.php'),
                "{{-- Do not delete me :) I'm used for auto-generation menu items --}}",
                sprintf(
                    "<li class=\"nav-item\"><a class=\"nav-link\" href=\"{{ url('admin/%s') }}\"><i class=\"nav-icon %s\"></i> {{ trans('admin.%s.title') }}</a></li>",
                    $this->resource,
                    $icon,
                    $this->modelLangFormat,
                ) . PHP_EOL . "           {{-- Do not delete me :) I'm used for auto-generation menu items --}}",
                '|url\(\'admin\/' . $this->resource . '\'\)|',
            )
        ) {
            $this->info('Updating sidebar');
        }
    }
}
