<?php namespace Brackets\AdminGenerator\Generate\Traits;

use Brackets\AdminGenerator\Generate\Controller;
use Brackets\AdminGenerator\Generate\Model;
use Illuminate\Support\Str;

trait Names
{

    public string $tableName;

    public string $modelBaseName;
    public string $modelFullName;
    public string $modelPlural;
    public string $modelVariableName;
    public string $modelRouteAndViewName;
    public string $modelNamespace;
    public string $modelWithNamespaceFromDefault;
    public string $modelViewsDirectory;
    public string $modelDotNotation;
    public string $modelJSName;
    public string $modelLangFormat;
    public string $resource;
    public string $exportBaseName;
    public string $titleSingular;
    public string $titlePlural;

    public string $controllerWithNamespaceFromDefault;

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
        $this->modelNamespace = Str::replaceLast("\\" . $this->modelBaseName, '', $this->modelFullName);
        if (!Str::startsWith($this->modelFullName,
            $startsWith = trim($modelGenerator->rootNamespace(), '\\') . '\Models\\')) {
            $this->modelWithNamespaceFromDefault = $this->modelBaseName;
        } else {
            $this->modelWithNamespaceFromDefault = Str::replaceFirst($startsWith, '', $this->modelFullName);
        }
        $this->modelViewsDirectory = Str::lower(Str::kebab(implode('/',
            collect(explode('\\', $this->modelWithNamespaceFromDefault))->map(function ($part) {
                return lcfirst($part);
            })->toArray())));

        $parts = collect(explode('\\', $this->modelWithNamespaceFromDefault));
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
        if (!Str::startsWith($controllerFullName,
            $startsWith = trim($controllerGenerator->rootNamespace(), '\\') . '\Http\\Controllers\\Admin\\')) {
            $this->controllerWithNamespaceFromDefault = $controllerFullName;
        } else {
            $this->controllerWithNamespaceFromDefault = Str::replaceFirst($startsWith, '', $controllerFullName);
        }

        if (!empty($modelWithFullNamespace)) {
            $this->modelFullName = $modelWithFullNamespace;
        }
        $this->exportBaseName = Str::studly($tableName) . 'Export';

        $this->titleSingular = Str::singular(str_replace(['_'], ' ', Str::title($this->tableName)));
        $this->titlePlural = str_replace(['_'], ' ', Str::title($this->tableName));
    }

    public function valueWithoutId(string $string): string
    {
        if (Str::endsWith(Str::lower($string), '_id')) {
            $string = Str::substr($string, 0, -3);
        }

        return Str::ucfirst(str_replace('_', ' ', $string));
    }

}
