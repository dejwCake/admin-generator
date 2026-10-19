<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\FileAppenders;

use Brackets\AdminGenerator\Dtos\GeneratedActions;
use Brackets\AdminGenerator\Enums\GeneratedAction;
use Brackets\AdminGenerator\Generators\Traits\ResolvesGeneratedActions;
use Override;
use Symfony\Component\Console\Input\InputOption;

final class Lang extends FileAppender
{
    use ResolvesGeneratedActions;

    protected GeneratedActions $generatedActions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:lang';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Append admin translations into a admin lang file';

    /**
     * Path for view
     */
    protected string $view = 'file-appenders.lang';

    /**
     * Lang has also export translation
     */
    public function handle(): void
    {
        $template = $this->option('template');
        $belongsToMany = $this->option('belongs-to-many');
        $media = $this->option('media');
        $locale = $this->option('locale');

//        //TODO check if exists
//        //TODO make global for all generator
//        //TODO also with prefix
        if ($template !== null) {
            $this->view = sprintf('file-appenders.templates.%s.lang', $template);
        }

        $this->relations = $this->relationBuilder->build($this->tableName, $belongsToMany);

        $this->generatedActions = $this->resolveGeneratedActions();

        if ($media !== null && $media !== []) {
            $this->mediaCollections = $this->mediaCollectionBuilder->build($media);
        }

        if ($locale === null) {
            $locale = 'en';
        }

        // TODO name-spaced model names should be probably inserted as a sub-array in a translation file..

        $markerText = "//-- Do not delete me :) I'm used for auto-generation language arrays --" . PHP_EOL;
        $defaultContent = "<?php" . PHP_EOL . PHP_EOL . "declare(strict_types=1);"
            . PHP_EOL . PHP_EOL . "return [" . PHP_EOL . sprintf('    %s', $markerText) . "];" . PHP_EOL;

        if (
            $this->replaceOrInsertBlock(
                $this->laravel->langPath(sprintf('%s/admin.php', $locale)),
                $this->modelLangFormat,
                $this->buildContent() . PHP_EOL,
                $markerText,
                $defaultContent,
            )
        ) {
            $this->call('cache:clear');
            $this->info('Appending translations finished');
        }
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a controller for the given model'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
            ...$this->getGeneratedActionOptions(),
            [
                'media',
                'M',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Media collections (format: name:type:disk:maxFiles[:maxFileSizeInMb])',
            ],
            ['locale', 'c', InputOption::VALUE_OPTIONAL, 'Specify custom locale'],
            [
                'translatable',
                'tr',
                InputOption::VALUE_OPTIONAL,
                'Comma-separated list of columns to treat as translatable (defaults to all json/jsonb columns)',
            ],
        ];
    }

    protected function buildContent(): string
    {
        $columns = $this->columnCollectionBuilder->build(
            $this->tableName,
            $this->modelVariableName,
            $this->extractTranslatable(),
        );

        return $this->viewFactory->make(sprintf('brackets/admin-generator::%s', $this->view), [
            //globals
            'modelLangFormat' => $this->modelLangFormat,
            'modelBaseName' => $this->modelBaseName,
            'titleSingular' => $this->titleSingular,
            'titlePlural' => $this->titlePlural,
            'relations' => $this->relations,
            //has
            'hasCreate' => $this->generatedActions->has(GeneratedAction::Create),
            'hasShow' => $this->generatedActions->has(GeneratedAction::Show),
            'hasEdit' => $this->generatedActions->has(GeneratedAction::Edit),
            'hasDelete' => $this->generatedActions->has(GeneratedAction::Delete),
            'hasBulkDelete' => $this->generatedActions->has(GeneratedAction::BulkDelete),
            'hasExport' => $this->generatedActions->has(GeneratedAction::Export),
            'hasProfile' => $this->tableName === 'admin_users',
            'hasPublishedAt' => $columns->hasByName('published_at'),
            //columns
            'columns' => $columns,
            //media
            'mediaCollections' => $this->mediaCollections,
        ])->render();
    }
}
