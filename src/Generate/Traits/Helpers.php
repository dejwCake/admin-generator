<?php namespace Brackets\AdminGenerator\Generate\Traits;

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait Helpers {

    public function option(?string $key = null) {
        return ($key === null || $this->hasOption($key)) ? parent::option($key) : null;
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
    protected function alreadyExists(string $path): bool
    {
        return $this->files->exists($path);
    }


    /**
     * Check if provided relation has a table
     */
    public function checkRelationTable(string $relationTable): bool
    {
        $schema = app(Builder::class);
        return $schema->hasTable($relationTable);
    }

    /**
     * Sets relation of belongs to many type
     */
    //TODO add other relation types
    public function setBelongToManyRelation(string $belongsToMany)
    {
        $this->relations['belongsToMany'] = (new Collection(explode(',', $belongsToMany)))->filter(function($belongToManyRelation) {
            return $this->checkRelationTable($belongToManyRelation);
        })->map(function($belongsToMany) {
            return [
                'current_table' => $this->tableName,
                'related_table' => $belongsToMany,
                'related_model' => ($belongsToMany == 'roles') ? "Spatie\\Permission\\Models\\Role" : "App\\Models\\". Str::studly(Str::singular($belongsToMany)),
                'related_model_class' => ($belongsToMany == 'roles') ? "Spatie\\Permission\\Models\\Role::class" : "App\\Models\\". Str::studly(Str::singular($belongsToMany)).'::class',
                'related_model_name' => Str::studly(Str::singular($belongsToMany)),
                'related_model_name_plural' => Str::studly($belongsToMany),
                'related_model_variable_name' => lcfirst(Str::singular(class_basename($belongsToMany))),
                'relation_table' => trim(collect([$this->tableName, $belongsToMany])->sortBy(function($table) {
                    return $table;
                })->reduce(function($relationTable, $table) {
                    return $relationTable.'_'.$table;
                }), '_'),
                'foreign_key' => Str::singular($this->tableName).'_id',
                'related_key' => Str::singular($belongsToMany).'_id',
            ];
        })->keyBy('related_table');
    }


    /**
     * Determine if the content is already present in the file
     */
    protected function alreadyAppended(string $path, string $content): bool
    {
        if (str_contains($this->files->get($path), $content)) {
            return true;
        }
        return false;
    }

}
