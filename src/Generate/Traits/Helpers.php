<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate\Traits;

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait Helpers
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingAnyTypeHint
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint
     */
    public function option($key = null)
    {
        return $key === null || $this->hasOption($key) ? parent::option($key) : null;
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
    public function setBelongToManyRelation(string $belongsToMany): void
    {
        $this->relations['belongsToMany'] = (new Collection(explode(',', $belongsToMany)))
            ->filter(fn (string $belongToManyRelation): bool => $this->checkRelationTable($belongToManyRelation))
            ->map(fn (string $belongsToMany): array => [
                    'current_table' => $this->tableName,
                    'related_table' => $belongsToMany,
                    'related_model' => $belongsToMany === 'roles'
                        ? 'Spatie\\Permission\\Models\\Role'
                        : 'App\\Models\\' . Str::studly(Str::singular($belongsToMany),),
                    'related_model_class' => $belongsToMany === 'roles'
                        ? 'Spatie\\Permission\\Models\\Role::class'
                        : 'App\\Models\\' . Str::studly(Str::singular($belongsToMany)) . '::class',
                    'related_model_name' => Str::studly(Str::singular($belongsToMany)),
                    'related_model_name_plural' => Str::studly($belongsToMany),
                    'related_model_variable_name' => lcfirst(Str::singular(class_basename($belongsToMany))),
                    'relation_table' => trim($this->getRelationTable($belongsToMany), '_'),
                    'foreign_key' => Str::singular($this->tableName) . '_id',
                    'related_key' => Str::singular($belongsToMany) . '_id',
                ])->keyBy('related_table')
            ->toArray();
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

    protected function getRelationTable(string $belongsToMany): string
    {
        return (string) (new Collection([$this->tableName, $belongsToMany]))
            ->sortBy(static fn (string $table): string => $table)
            ->reduce(
                static fn (string $relationTable, string $table): string => $relationTable . '_' . $table,
                '',
            );
    }
}
