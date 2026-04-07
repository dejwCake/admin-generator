<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Brackets\AdminGenerator\Dtos\Media\MediaCollection;
use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
use Illuminate\Support\Collection;
use Override;
use Symfony\Component\Console\Input\InputOption;

final class BladeForm extends ResourceGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:blade-form';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate a form blade view';

    /**
     * Path for blade view
     */
    protected string $view = 'blade-form';

    /**
     * Name of view, will be used in directory
     */
    protected string $fileName = '';

    /**
     * Route to process form
     */
    protected ?string $route = null;

    protected string $formJsRelativePath = '';

    public function handle(): void
    {
        $force = (bool) $this->option('force');
        $template = $this->option('template');
        $fileName = $this->option('file-name');
        $this->route = $this->option('route');

        if ($template !== null) {
            $this->view = 'templates.' . $template . '.blade-form';
        }

        $belongsToMany = $this->option('belongs-to-many');

        $this->relations = $this->relationBuilder->build($this->tableName, $belongsToMany);

        $this->fileName = $fileName ?: $this->modelViewsDirectory;
        $this->formJsRelativePath = str_replace([DIRECTORY_SEPARATOR, '/', '\\'], '-', $this->fileName);
        if (!$fileName) {
            $this->fileName .= DIRECTORY_SEPARATOR . 'form';
        }

        if (!$this->route) {
            $this->route = $fileName
                ? 'admin/' . $this->fileName
                : 'admin/' . $this->resource . '/update';
        }

        $this->generate($force);
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating full form'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['file-name', 'nm', InputOption::VALUE_OPTIONAL, 'Specify a blade file path'],
            ['route', 'r', InputOption::VALUE_OPTIONAL, 'Specify custom route for form'],
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
        ];
    }

    /** @return array<string, Collection|RelationCollection|ColumnCollection|array<string>|string|bool> */
    private function getCommonViewData(ColumnCollection $columns): array
    {
        $visibleColumns = $columns->getVisible();

        $hasCreatedByAdminUser = $columns->hasByName('created_by_admin_user_id');
        $hasUpdatedByAdminUser = $columns->hasByName('updated_by_admin_user_id');

        // Columns to display in the main form body (excluding sidebar/system columns)
        $leftFormColumns = $visibleColumns->rejectByName(
            'published_at',
            'created_by_admin_user_id',
            'updated_by_admin_user_id',
        );
        // Right column: only published_at
        $publishedColumns = $visibleColumns->filterByName('published_at');

        // Split media collections: gallery goes right, rest left
        $galleryCollections = $this->mediaCollections->filter(
            static fn (object $collection): bool => $collection->collectionName === 'gallery',
        );

        return [
            //globals
            'modelBaseName' => $this->modelBaseName,
            'modelPlural' => $this->modelPlural,
            'modelVariableName' => $this->modelVariableName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelDotNotation' => $this->modelDotNotation,
            'modelJSName' => $this->formJsRelativePath,
            'modelLangFormat' => $this->modelLangFormat,
            'resource' => $this->resource,
            'mediaCollections' => $this->mediaCollections,
            'relations' => $this->relations,
            //has
            'hasCreatedByAdminUser' => $hasCreatedByAdminUser,
            'hasUpdatedByAdminUser' => $hasUpdatedByAdminUser,
            'hasTranslatable' => $columns->hasByMajorType('json'),
            'hasPublishedAt' => $columns->hasByName('published_at'),
            'hasWysiwyg' => $leftFormColumns->hasWysiwyg(),
            'hasPassword' => $leftFormColumns->hasByName('password'),
            'hasEmail' => $leftFormColumns->hasByName('email'),
            'hasLanguage' => $leftFormColumns->hasByName('language'),
            'hasBoolColumns' => $leftFormColumns->hasByMajorType('bool'),
            'hasDateColumns' => $leftFormColumns->hasByMajorType('date'),
            'hasTimeColumns' => $leftFormColumns->hasByMajorType('time'),
            'hasDatetimeColumns' => $leftFormColumns->hasByMajorType('datetime'),
            'hasFormInput' => $leftFormColumns->hasFormInput(),
            'hasTextarea' => $leftFormColumns->hasTextarea(),
            'hasLocalizedInput' => $leftFormColumns->hasLocalizedInput(),
            'hasLocalizedWysiwyg' => $leftFormColumns->hasLocalizedWysiwyg(),
            //columns
            'columns' => $visibleColumns,
            'leftFormColumns' => $leftFormColumns,
            'leftMediaCollections' => $this->mediaCollections->reject(
                static fn (MediaCollection $mediaCollection): bool => $mediaCollection->collectionName === 'gallery',
            ),
            'publishedColumns' => $publishedColumns,
            'galleryCollections' => $galleryCollections,
            'wysiwygTextColumnNames' => $columns->getWysiwygColumnNames(),

            'isUsedTwoColumnsLayout' => $publishedColumns->isNotEmpty()
                || $galleryCollections->isNotEmpty()
                || $hasCreatedByAdminUser
                || $hasUpdatedByAdminUser,

            'profileColumns' => $leftFormColumns->rejectByName('password', 'activated', 'forbidden'),
        ];
    }

    private function build(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);
        $data = $this->getCommonViewData($columns);

        $data['modelLabelColumn'] = $columns->getLabelColumn();

        $data['route'] = $this->route;

        return view('brackets/admin-generator::' . $this->view, $data)->render();
    }

    private function generate(bool $force): void
    {
        $viewPath = resource_path('views/admin/' . $this->fileName . '.blade.php');

        if ($this->alreadyExists($viewPath) && !$force) {
            $this->error('File ' . $viewPath . ' already exists!');

            return;
        }

        if ($this->alreadyExists($viewPath) && $force) {
            $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
            $this->files->delete($viewPath);
        }

        $this->makeDirectory($viewPath);

        $this->files->put($viewPath, $this->build());
        $this->info('Generating ' . $viewPath . ' finished');
    }
}
