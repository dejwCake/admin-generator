<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate\Traits;

use Illuminate\Database\Schema\Builder as Schema;
use Illuminate\Support\Collection;

trait Columns
{
    /** @return Collection<array<string, string|bool>> */
    protected function readColumnsFromTable(string $tableName): Collection
    {
        $schema = app(Schema::class);
        // TODO how to process jsonb & json translatable columns? need to figure it out

        $indexes = new Collection($schema->getIndexes($tableName));

        return (new Collection(
            $schema->getColumns($tableName),
        ))->map(
            static function (array $column) use ($tableName, $indexes, $schema): array {
                //Checked unique index
                $columnUniqueIndexes = $indexes->filter(static fn (array $index): bool
                => in_array($column['name'], $index['columns'], true) && ($index['unique'] && !$index['primary']));
                $columnUniqueDeleteAtCondition = $columnUniqueIndexes->filter(
                    static fn (array $index): bool => str_contains($index['name'], 'null_deleted_at'),
                );
                // TODO add foreign key

                return [
                    'name' => $column['name'],
                    'type' => $schema->getColumnType($tableName, $column['name']),
                    'required' => $column['nullable'] === false,
                    'unique' => $columnUniqueIndexes->count() > 0,
                    'unique_deleted_at_condition' => $columnUniqueDeleteAtCondition->count() > 0,
                ];
            },
        );
    }

    /** @return Collection<string, string|array<string>> */
    protected function getVisibleColumns(string $tableName, string $modelVariableName): Collection
    {
        $columns = $this->readColumnsFromTable($tableName);
        $hasSoftDelete = (
            $columns->filter(static fn (array $column): bool => $column['name'] === 'deleted_at')
                ->count() > 0
        );

        return $columns->filter(static fn (array $column): bool => !in_array(
            $column['name'],
            ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'last_login_at'],
            true,
        ))->map(fn (array $column): array => [
                'name' => $column['name'],
                'type' => $column['type'],
                'serverStoreRules' => $this->getServerStoreRules($column, $tableName, $hasSoftDelete)->toArray(),
                'serverUpdateRules' => $this->getServerUpdateRules(
                    $column,
                    $tableName,
                    $modelVariableName,
                    $hasSoftDelete,
                )
                    ->toArray(),
                'frontendRules' => $this->getFrontendRules($column)->toArray(),
            ]);
    }

    /**
     * @param array<string, string|bool> $column
     */
    protected function getServerStoreRules(array $column, string $tableName, bool $hasSoftDelete): Collection
    {
        $serverStoreRules = new Collection([]);
        $serverStoreRules = $this->getServerStoreRulesByRequire($column['required'], $serverStoreRules);
        $serverStoreRules = $this->getServerStoreRulesByName($column['name'], $serverStoreRules);
        $serverStoreRules = $this->getServerStoreRulesByUnique($column, $tableName, $hasSoftDelete, $serverStoreRules);
        $serverStoreRules = $this->getServerStoreRulesByUniqueJson(
            $column,
            $tableName,
            $hasSoftDelete,
            $serverStoreRules,
        );

        return $this->getServerStoreRulesByType($column['type'], $serverStoreRules);
    }

    /**
     * @param array<string, string|bool> $column
     */
    protected function getServerUpdateRules(
        array $column,
        string $tableName,
        string $modelVariableName,
        bool $hasSoftDelete,
    ): Collection {
        $serverUpdateRules = new Collection([]);
        $serverUpdateRules = $this->getServerUpdateRulesByRequire($column['required'], $serverUpdateRules);
        $serverUpdateRules = $this->getServerUpdateRulesByName($column['name'], $serverUpdateRules);
        $serverUpdateRules = $this->getServerUpdateRulesByUnique(
            $column,
            $tableName,
            $modelVariableName,
            $hasSoftDelete,
            $serverUpdateRules,
        );
        $serverUpdateRules = $this->getServerUpdateRulesByUniqueJson(
            $column,
            $tableName,
            $modelVariableName,
            $hasSoftDelete,
            $serverUpdateRules,
        );

        return $this->getServerUpdateRulesByType($column['type'], $serverUpdateRules);
    }

    /**
     * @param array<string, string|bool> $column
     */
    protected function getFrontendRules(array $column): Collection
    {
        $frontendRules = new Collection([]);
        $frontendRules = $this->getFrontendRulesByRequire($column, $frontendRules);
        $frontendRules = $this->getFrontendRulesByName($column['name'], $frontendRules);

        return $this->getFrontendRulesByType($column['type'], $frontendRules);
    }

    protected function getServerStoreRulesByRequire(bool $required, Collection $serverStoreRules): Collection
    {
        if ($required) {
            $serverStoreRules->push('\'required\'');
        } else {
            $serverStoreRules->push('\'nullable\'');
        }

        return $serverStoreRules;
    }

    protected function getServerUpdateRulesByRequire(bool $required, Collection $serverUpdateRules): Collection
    {
        if ($required) {
            $serverUpdateRules->push('\'sometimes\'');
        } else {
            $serverUpdateRules->push('\'nullable\'');
        }

        return $serverUpdateRules;
    }

    /**
     * @param array<string, string|bool> $column
     */
    protected function getFrontendRulesByRequire(array $column, Collection $frontendRules): Collection
    {
        if ($column['required']
            && $column['type'] !== 'boolean'
            && $column['type'] !== 'bool'
            && $column['name'] !== 'password'
        ) {
            $frontendRules->push('required');
        }

        return $frontendRules;
    }

    protected function getServerStoreRulesByName(string $name, Collection $serverStoreRules): Collection
    {
        if ($name === 'email') {
            $serverStoreRules->push('\'email\'');
        }

        if ($name === 'password') {
            $serverStoreRules->push('\'confirmed\'');
            $serverStoreRules->push('\'min:7\'');
            $serverStoreRules->push('\'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9]).*$/\'');
        }

        return $serverStoreRules;
    }

    protected function getServerUpdateRulesByName(string $name, Collection $serverUpdateRules): Collection
    {
        if ($name === 'email') {
            $serverUpdateRules->push('\'email\'');
        }

        if ($name === 'password') {
            $serverUpdateRules->push('\'confirmed\'');
            $serverUpdateRules->push('\'min:7\'');
            $serverUpdateRules->push('\'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9]).*$/\'');
        }

        return $serverUpdateRules;
    }

    protected function getFrontendRulesByName(string $name, Collection $frontendRules): Collection
    {
        if ($name === 'email') {
            $frontendRules->push('email');
        }

        if ($name === 'password') {
            $frontendRules->push('confirmed:password');
            $frontendRules->push('min:7');
            //TODO not working, need fixing
//            $frontendRules->push('regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[!$#%]).*$/g');
        }

        return $frontendRules;
    }

    /**
     * @param array<string, string|bool> $column
     */
    protected function getServerStoreRulesByUnique(
        array $column,
        string $tableName,
        bool $hasSoftDelete,
        Collection $serverStoreRules,
    ): Collection {
        if ($column['type'] === 'json') {
            return $serverStoreRules;
        }

        if ($column['unique'] || $column['name'] === 'slug') {
            $storeRule = 'Rule::unique(\'' . $tableName . '\', \'' . $column['name'] . '\')';
            if ($hasSoftDelete && $column['unique_deleted_at_condition']) {
                $storeRule .= '->whereNull(\'deleted_at\')';
            }
            $serverStoreRules->push($storeRule);
        }

        return $serverStoreRules;
    }

    /**
     * @param array<string, string|bool> $column
     */
    protected function getServerUpdateRulesByUnique(
        array $column,
        string $tableName,
        string $modelVariableName,
        bool $hasSoftDelete,
        Collection $serverUpdateRules,
    ): Collection {
        if ($column['type'] === 'json') {
            return $serverUpdateRules;
        }

        if ($column['unique'] || $column['name'] === 'slug') {
            $updateRule = 'Rule::unique(\'' . $tableName . '\', \'' . $column['name'] . '\')->ignore($this->'
                . $modelVariableName . '->getKey(), $this->' . $modelVariableName . '->getKeyName())';
            if ($hasSoftDelete && $column['unique_deleted_at_condition']) {
                $updateRule .= '->whereNull(\'deleted_at\')';
            }
            $serverUpdateRules->push($updateRule);
        }

        return $serverUpdateRules;
    }

    /**
     * @param array<string, string|bool> $column
     */
    protected function getServerStoreRulesByUniqueJson(
        array $column,
        string $tableName,
        bool $hasSoftDelete,
        Collection $serverStoreRules,
    ): Collection {
        if ($column['type'] !== 'json') {
            return $serverStoreRules;
        }

        if ($column['unique'] || $column['name'] === 'slug') {
            $storeRule = 'Rule::unique(\'' . $tableName . '\', \'' . $column['name'] . '->\'.$locale)';
            if ($hasSoftDelete && $column['unique_deleted_at_condition']) {
                $storeRule .= '->whereNull(\'deleted_at\')';
            }
            $serverStoreRules->push($storeRule);
        }

        return $serverStoreRules;
    }

    /**
     * @param array<string, string|bool> $column
     */
    protected function getServerUpdateRulesByUniqueJson(
        array $column,
        string $tableName,
        string $modelVariableName,
        bool $hasSoftDelete,
        Collection $serverUpdateRules,
    ): Collection {
        if ($column['type'] !== 'json') {
            return $serverUpdateRules;
        }

        if ($column['unique'] || $column['name'] === 'slug') {
            $updateRule = 'Rule::unique(\'' . $tableName . '\', \'' . $column['name'] . '->\'.$locale)->ignore($this->'
                . $modelVariableName . '->getKey(), $this->' . $modelVariableName . '->getKeyName())';
            if ($hasSoftDelete && $column['unique_deleted_at_condition']) {
                $updateRule .= '->whereNull(\'deleted_at\')';
            }
            $serverUpdateRules->push($updateRule);
        }

        return $serverUpdateRules;
    }

    protected function getServerStoreRulesByType(string $type, Collection $serverStoreRules): Collection
    {
        $rule = match ($type) {
            'datetime',
            'date' => '\'date\'',
            'time' => '\'date_format:H:i:s\'',
            'integer',
            'tinyInteger',
            'smallInteger',
            'mediumInteger',
            'bigInteger',
            'unsignedInteger',
            'unsignedTinyInteger',
            'unsignedSmallInteger',
            'unsignedMediumInteger',
            'unsignedBigInteger' => '\'integer\'',
            'bool',
            'boolean' => '\'boolean\'',
            'float',
            'decimal' => '\'numeric\'',
            default => '\'string\'',
        };

        return $serverStoreRules->push($rule);
    }

    protected function getServerUpdateRulesByType(string $type, Collection $serverUpdateRules): Collection
    {
        $rule = match ($type) {
            'datetime',
            'date' => '\'date\'',
            'time' => '\'date_format:H:i:s\'',
            'integer',
            'tinyInteger',
            'smallInteger',
            'mediumInteger',
            'bigInteger',
            'unsignedInteger',
            'unsignedTinyInteger',
            'unsignedSmallInteger',
            'unsignedMediumInteger',
            'unsignedBigInteger' => '\'integer\'',
            'bool',
            'boolean' => '\'boolean\'',
            'float',
            'decimal' => '\'numeric\'',
            default => '\'string\'',
        };

        return $serverUpdateRules->push($rule);
    }

    protected function getFrontendRulesByType(string $type, Collection $frontendRules): Collection
    {
        $rule = match ($type) {
            'datetime',
            'date' => 'date_format:yyyy-MM-dd HH:mm:ss',
            'time' => 'date_format:HH:mm:ss',
            'integer',
            'tinyInteger',
            'smallInteger',
            'mediumInteger',
            'bigInteger',
            'unsignedInteger',
            'unsignedTinyInteger',
            'unsignedSmallInteger',
            'unsignedMediumInteger',
            'unsignedBigInteger' => 'integer',
            'bool',
            'boolean' => '',
            'float',
                // FIXME?? I'm not sure about this one
            'decimal' => 'decimal',
            default => null,
        };

        if ($rule !== null) {
            $frontendRules->push($rule);
        }

        return $frontendRules;
    }
}
