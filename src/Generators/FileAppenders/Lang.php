<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\FileAppenders;

use Override;
use Symfony\Component\Console\Input\InputOption;

final class Lang extends FileAppender
{
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
    protected string $view = 'lang';

    /**
     * Lang has also export translation
     */
    protected bool $export = false;

    public function handle(): void
    {
        $template = $this->option('template');
        $belongsToMany = $this->option('belongs-to-many');
        $withExport = $this->option('with-export');
        $media = $this->option('media');
        $locale = $this->option('locale');

//        //TODO check if exists
//        //TODO make global for all generator
//        //TODO also with prefix
        if ($template !== null) {
            $this->view = 'templates.' . $template . '.lang';
        }

        if ($belongsToMany !== null) {
            $this->setBelongToManyRelation($belongsToMany);
        }

        if ($withExport) {
            $this->export = true;
        }

        if ($media !== null && $media !== []) {
            $this->setMediaCollections($media);
        }

        if ($locale === null) {
            $locale = 'en';
        }

        // TODO name-spaced model names should be probably inserted as a sub-array in a translation file..

        $markerText = "// Do not delete me :) I'm used for auto-generation" . PHP_EOL;
        $defaultContent = "<?php" . PHP_EOL . PHP_EOL . "declare(strict_types=1);"
            . PHP_EOL . PHP_EOL . "return [" . PHP_EOL . "    " . $markerText . "];" . PHP_EOL;

        if (
            $this->replaceOrInsertBlock(
                lang_path($locale . '/admin.php'),
                $this->modelLangFormat,
                $this->buildClass() . PHP_EOL,
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
            ['with-export', 'e', InputOption::VALUE_NONE, 'Generate an option to Export as Excel'],
            [
                'media',
                'M',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Media collections (format: name:type:disk:maxFiles)',
            ],
            ['locale', 'c', InputOption::VALUE_OPTIONAL, 'Specify custom locale'],
        ];
    }

    private function buildClass(): string
    {
        $columns = $this->readColumnsFromTable($this->tableName);

        return view('brackets/admin-generator::' . $this->view, [
            'modelLangFormat' => $this->modelLangFormat,
            'modelBaseName' => $this->modelBaseName,
            'modelPlural' => $this->modelPlural,
            'titleSingular' => $this->titleSingular,
            'titlePlural' => $this->titlePlural,
            'export' => $this->export,
            'hasPublishedAt' => $columns->contains(
                static fn (array $column): bool => $column['name'] === 'published_at',
            ),
            'hasProfile' => $this->tableName === 'admin_users',

            'columns' => $columns,
            'relations' => $this->relations,
            'mediaCollections' => $this->mediaCollections,
        ])->render();
    }
}
