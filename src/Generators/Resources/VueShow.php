<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Override;
use Symfony\Component\Console\Input\InputOption;

use function sprintf;

final class VueShow extends ResourceGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:vue-show';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate a show vue component';

    protected string $view = 'resources.vue-show';

    public function handle(): void
    {
        $force = (bool) $this->option('force');
        $template = $this->option('template');

        if ($template !== null) {
            $this->view = sprintf('resources.templates.%s.vue-show', $template);
        }

        $path = $this->laravel->resourcePath(sprintf('js/admin/%s/Show.vue', $this->modelJSName));

        $this->generate($path, $force);
        $this->registerVueComponent(
            sprintf('%sShow', Str::studly($this->modelJSName)),
            $this->modelJSName,
            'Show.vue',
        );
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating form'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
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
        )->getForShow();

        $dateImports = new Collection();
        if ($columns->hasByMajorType('date')) {
            $dateImports->push('formatDate');
        }
        if ($columns->hasByMajorType('time')) {
            $dateImports->push('formatTime');
        }
        if ($columns->hasByMajorType('datetime')) {
            $dateImports->push('formatDatetime');
        }

        return $this->viewFactory->make(sprintf('brackets/admin-generator::%s', $this->view), [
            //has
            'hasBoolean' => $columns->hasByMajorType('bool'),
            //columns
            'columns' => $columns,
            //other
            'dateImports' => $dateImports->sort(),
        ])->render();
    }
}
