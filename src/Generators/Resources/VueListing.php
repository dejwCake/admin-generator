<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Brackets\AdminGenerator\Dtos\GeneratedActions;
use Brackets\AdminGenerator\Enums\GeneratedAction;
use Brackets\AdminGenerator\Generators\Traits\ResolvesGeneratedActions;
use Illuminate\Support\Collection;
use Override;
use Symfony\Component\Console\Input\InputOption;

final class VueListing extends ResourceGenerator
{
    use ResolvesGeneratedActions;

    protected GeneratedActions $generatedActions;

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

    protected string $view = 'resources.vue-listing';

    public function handle(): void
    {
        $force = (bool) $this->option('force');
        $template = $this->option('template');

        if ($template !== null) {
            $this->view = sprintf('resources.templates.%s.vue-listing', $template);
        }

        $this->generatedActions = $this->resolveGeneratedActions();

        $this->relations = $this->relationBuilder->build($this->tableName, null);

        $path = $this->laravel->resourcePath(sprintf('js/admin/%s/Listing.vue', $this->modelJSName));

        $this->generate($path, $force);
        $this->registerVueComponent(sprintf('%sListing', $this->modelBaseName), $this->modelJSName, 'Listing.vue');
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating listing'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ...$this->getGeneratedActionOptions(),
            [
                'translatable',
                'tr',
                InputOption::VALUE_OPTIONAL,
                'Comma-separated list of columns to treat as translatable (defaults to all json/jsonb columns)',
            ],
        ];
    }

    #[Override]
    protected function buildView(): string
    {
        $allColumns = $this->columnCollectionBuilder->build(
            $this->tableName,
            $this->modelVariableName,
            $this->extractTranslatable(),
        );
        $columns = $allColumns->getForIndex();

        $hasSoftDelete = $allColumns->hasByName('deleted_at') && $this->option('template') === null;
        $hasTrash = $hasSoftDelete && $this->generatedActions->has(GeneratedAction::Delete);
        $hasBulkTrash = $hasSoftDelete && $this->generatedActions->has(GeneratedAction::BulkDelete);

        $hasPublishedAt = $columns->hasByName('published_at');
        $hasUserDetailTooltip = $columns->hasByName('created_by_admin_user_id')
            || $columns->hasByName('updated_by_admin_user_id');

        $dateImports = $this->resolveDateImports(
            $columns,
            $hasPublishedAt || $hasUserDetailTooltip || $hasTrash || $hasBulkTrash,
        );

        return $this->viewFactory->make(sprintf('brackets/admin-generator::%s', $this->view), [
            //globals
            'relations' => $this->relations,
            //has
            'hasCreate' => $this->generatedActions->has(GeneratedAction::Create),
            'hasShow' => $this->generatedActions->has(GeneratedAction::Show),
            'hasEdit' => $this->generatedActions->has(GeneratedAction::Edit),
            'hasDelete' => $this->generatedActions->has(GeneratedAction::Delete),
            'hasBulkDelete' => $this->generatedActions->has(GeneratedAction::BulkDelete),
            'hasExport' => $this->generatedActions->has(GeneratedAction::Export),
            'hasTrash' => $hasTrash,
            'hasBulkTrash' => $hasBulkTrash,
            'hasPublishedAt' => $hasPublishedAt,
            'hasUserDetailTooltip' => $hasUserDetailTooltip,
            'hasSwitchColumns' => $columns->hasByMajorType('bool'),
            'hasDateColumns' => $dateImports->isNotEmpty(),
            //columns
            'columns' => $columns,
            //other
            'dateImports' => $dateImports,
            //deprecated alias for templates overridden from 2.4.0, use hasBulkDelete instead
            'hasBulk' => $this->generatedActions->has(GeneratedAction::BulkDelete),
        ])->render();
    }

    /**
     * @return Collection<int, string>
     */
    private function resolveDateImports(ColumnCollection $columns, bool $needsDatetime): Collection
    {
        $dateImports = new Collection();
        if ($columns->hasByMajorType('date')) {
            $dateImports->push('formatDate');
        }
        if ($columns->hasByMajorType('time')) {
            $dateImports->push('formatTime');
        }
        if ($needsDatetime || $columns->hasByMajorType('datetime')) {
            $dateImports->push('formatDatetime');
        }

        return $dateImports->sort();
    }
}
