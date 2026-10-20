<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Brackets\AdminGenerator\Dtos\GeneratedActions;
use Brackets\AdminGenerator\Enums\GeneratedAction;
use Brackets\AdminGenerator\Generators\Traits\ResolvesGeneratedActions;
use Override;
use Symfony\Component\Console\Input\InputOption;

final class BladeIndex extends ResourceGenerator
{
    use ResolvesGeneratedActions;

    protected GeneratedActions $generatedActions;

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

    protected string $view = 'resources.blade-index';

    public function handle(): void
    {
        $force = (bool) $this->option('force');
        $template = $this->option('template');

        if ($template !== null) {
            $this->view = sprintf('resources.templates.%s.blade-index', $template);
        }

        $this->generatedActions = $this->resolveGeneratedActions();

        $path = $this->laravel->resourcePath(sprintf('views/admin/%s/index.blade.php', $this->modelViewsDirectory));

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
        $columns = $this->columnCollectionBuilder->build(
            $this->tableName,
            $this->modelVariableName,
            $this->extractTranslatable(),
        )->getForIndex();

        return $this->viewFactory->make(sprintf('brackets/admin-generator::%s', $this->view), [
            //globals
            'modelJSName' => $this->modelJSName,
            'modelLangFormat' => $this->modelLangFormat,
            //has
            'hasCreate' => $this->generatedActions->has(GeneratedAction::Create),
            'hasShow' => $this->generatedActions->has(GeneratedAction::Show),
            'hasEdit' => $this->generatedActions->has(GeneratedAction::Edit),
            'hasDelete' => $this->generatedActions->has(GeneratedAction::Delete),
            'hasBulkDelete' => $this->generatedActions->has(GeneratedAction::BulkDelete),
            'hasExport' => $this->generatedActions->has(GeneratedAction::Export),
            'hasPublishedAt' => $columns->hasByName('published_at'),
            'hasCreatedByAdminUser' => $columns->hasByName('created_by_admin_user_id'),
            'hasUpdatedByAdminUser' => $columns->hasByName('updated_by_admin_user_id'),
            //columns
            'columns' => $columns,
            //deprecated alias for templates overridden from 2.4.0, use hasBulkDelete instead
            'hasBulk' => $this->generatedActions->has(GeneratedAction::BulkDelete),
        ])->render();
    }
}
