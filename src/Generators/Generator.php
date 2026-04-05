<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators;

use Brackets\AdminGenerator\Builders\ColumnCollectionBuilder;
use Brackets\AdminGenerator\Builders\MediaCollectionBuilder;
use Brackets\AdminGenerator\Builders\RelationBuilder;
use Brackets\AdminGenerator\Dtos\Media\MediaCollection;
use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
use Brackets\AdminGenerator\Generators\Classes\Controller;
use Brackets\AdminGenerator\Generators\Classes\Model;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class Generator extends Command
{
    protected string $tableName;

    protected string $modelBaseName;
    protected string $modelFullName;
    protected string $modelPlural;
    protected string $modelVariableName;
    protected string $modelRouteAndViewName;
    protected string $modelNamespace;
    protected string $modelWithNamespaceFromDefault;
    protected string $modelViewsDirectory;
    protected string $modelDotNotation;
    protected string $modelJSName;
    protected string $modelLangFormat;
    protected string $resource;
    protected string $exportBaseName;
    protected string $titleSingular;
    protected string $titlePlural;

    protected string $controllerFullName;
    protected string $controllerWithNamespaceFromDefault;

    protected RelationCollection $relations;

    /** @var Collection<string, MediaCollection> */
    protected Collection $mediaCollections;

    /**
     * Create a new controller creator command instance.
     */
    public function __construct(
        protected readonly Filesystem $files,
        protected readonly ColumnCollectionBuilder $columnCollectionBuilder,
        protected readonly MediaCollectionBuilder $mediaCollectionBuilder,
        protected readonly RelationBuilder $relationBuilder,
    ) {
        parent::__construct();

        $this->relations = new RelationCollection();
        $this->mediaCollections = new Collection();
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingAnyTypeHint
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint
     */
    public function option($key = null)
    {
        return $key === null || $this->hasOption($key) ? parent::option($key) : null;
    }

    protected function initCommonNames(
        string $tableName,
        ?string $modelName = null,
        ?string $controllerName = null,
        ?string $modelWithFullNamespace = null,
    ): void {
        $this->tableName = $tableName;

        if ($this instanceof Model) {
            $modelGenerator = $this;
        } else {
            $modelGenerator = app(Model::class);
            $modelGenerator->setLaravel($this->laravel);
        }

        if (is_null($modelName)) {
            $modelName = $modelGenerator->generateClassNameFromTable($this->tableName);
        }
        $this->modelFullName = $modelGenerator->qualifyClass($modelName);

        $this->modelBaseName = class_basename($modelName);
        $this->modelPlural = Str::plural(class_basename($modelName));
        $this->modelVariableName = Str::lcfirst(Str::singular(class_basename($this->modelBaseName)));
        $this->modelRouteAndViewName = Str::lower(Str::kebab($this->modelBaseName));
        $this->modelNamespace = Str::replaceLast('\\' . $this->modelBaseName, '', $this->modelFullName);
        $this->modelWithNamespaceFromDefault =
            !Str::startsWith(
                $this->modelFullName,
                $startsWith = trim($modelGenerator->rootNamespace(), '\\') . '\Models\\',
            )
                ? $this->modelBaseName : Str::replaceFirst($startsWith, '', $this->modelFullName);
        $this->modelViewsDirectory = Str::lower(Str::kebab(implode(
            '/',
            (new Collection(explode('\\', $this->modelWithNamespaceFromDefault)))
                ->map(static fn (string $part) => lcfirst($part))
                ->toArray(),
        )));

        $parts = new Collection(explode('\\', $this->modelWithNamespaceFromDefault));
        $parts->pop();
        $parts->push($this->modelPlural);
        $this->resource = Str::lower(Str::kebab(implode('', $parts->toArray())));

        $this->modelDotNotation = str_replace('/', '.', $this->modelViewsDirectory);
        $this->modelJSName = str_replace('/', '-', $this->modelViewsDirectory);
        $this->modelLangFormat = str_replace('/', '_', $this->modelViewsDirectory);

        if ($this instanceof Controller) {
            $controllerGenerator = $this;
        } else {
            $controllerGenerator = app(Controller::class);
            $controllerGenerator->setLaravel($this->laravel);
        }

        if (is_null($controllerName)) {
            $controllerName = $controllerGenerator->generateClassNameFromTable($this->tableName);
        }

        $this->controllerFullName = $controllerGenerator->qualifyClass($controllerName);
        $this->controllerWithNamespaceFromDefault =
            !Str::startsWith(
                $this->controllerFullName,
                $startsWith = trim($controllerGenerator->rootNamespace(), '\\') . '\Http\\Controllers\\Admin\\',
            )
                ? $this->controllerFullName : Str::replaceFirst($startsWith, '', $this->controllerFullName);

        if ($modelWithFullNamespace !== null) {
            $this->modelFullName = $modelWithFullNamespace;
        }
        $this->exportBaseName = Str::studly($tableName) . 'Export';

        $this->titleSingular = Str::singular(str_replace(['_'], ' ', Str::title($this->tableName)));
        $this->titlePlural = str_replace(['_'], ' ', Str::title($this->tableName));
    }

    /**
     * Build the directory for the class if necessary.
     */
    protected function makeDirectory(string $path): string
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    /**
     * Determine if the file already exists.
     */
    protected function alreadyExists(string $path): bool|string
    {
        return $this->files->exists($path);
    }

    /**
     * Determine if the content is already present in the file
     */
    protected function alreadyAppended(string $path, string $content): bool
    {
        return str_contains($this->files->get($path), $content);
    }
}
