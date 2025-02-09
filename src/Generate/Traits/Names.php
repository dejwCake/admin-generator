<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate\Traits;

use Brackets\AdminGenerator\Generate\Controller;
use Brackets\AdminGenerator\Generate\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait Names
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

    protected string $controllerWithNamespaceFromDefault;

    public function valueWithoutId(string $string): string
    {
        if (Str::endsWith(Str::lower($string), '_id')) {
            $string = Str::substr($string, 0, -3);
        }

        return Str::ucfirst(str_replace('_', ' ', $string));
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
        $this->modelVariableName = lcfirst(Str::singular(class_basename($this->modelBaseName)));
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

        $controllerFullName = $controllerGenerator->qualifyClass($controllerName);
        $this->controllerWithNamespaceFromDefault =
            !Str::startsWith(
                $controllerFullName,
                $startsWith = trim($controllerGenerator->rootNamespace(), '\\') . '\Http\\Controllers\\Admin\\',
            )
         ? $controllerFullName : Str::replaceFirst($startsWith, '', $controllerFullName);

        if ($modelWithFullNamespace !== null) {
            $this->modelFullName = $modelWithFullNamespace;
        }
        $this->exportBaseName = Str::studly($tableName) . 'Export';

        $this->titleSingular = Str::singular(str_replace(['_'], ' ', Str::title($this->tableName)));
        $this->titlePlural = str_replace(['_'], ' ', Str::title($this->tableName));
    }
}
