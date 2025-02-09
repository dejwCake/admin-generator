<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate\Traits;

use Illuminate\Database\Schema\Builder as Schema;
use Illuminate\Support\Collection;

trait Columns
{
    /** @return Collection<string, string|bool> */
    protected function readColumnsFromTable(string $tableName): Collection
    {
        $schema = app(Schema::class);
        // TODO how to process jsonb & json translatable columns? need to figure it out

        $indexes = new Collection($schema->getIndexes($tableName));

        return (new Collection(
            $schema->getColumns($tableName),
        ))->map(
            static function ($column) use ($tableName, $indexes, $schema) {

                //Checked unique index
                $columnUniqueIndexes = $indexes->filter(static fn ($index) => in_array(
                    $column['name'],
                    $index['columns'],
                    true,
                ) && ($index['unique'] && !$index['primary']));
                $columnUniqueDeleteAtCondition = $columnUniqueIndexes->filter(
                    static fn ($index) => str_contains($index['name'], 'null_deleted_at'),
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

    /** @return Collection<string, string|array> */
    protected function getVisibleColumns(string $tableName, string $modelVariableName): Collection
    {
        $columns = $this->readColumnsFromTable($tableName);
        $hasSoftDelete = ($columns->filter(static fn ($column) => $column['name'] === "deleted_at")->count() > 0);

        return $columns->filter(static fn ($column) => !in_array(
            $column['name'],
            ["id", "created_at", "updated_at", "deleted_at", "remember_token", "last_login_at"],
        ))->map(static function ($column) use ($tableName, $hasSoftDelete, $modelVariableName) {
                $serverStoreRules = new Collection([]);
                $serverUpdateRules = new Collection([]);
                $frontendRules = new Collection([]);
            if ($column['required']) {
                $serverStoreRules->push('\'required\'');
                $serverUpdateRules->push('\'sometimes\'');
                if ($column['type'] !== 'boolean' && $column['name'] !== 'password') {
                    $frontendRules->push('required');
                }
            } else {
                $serverStoreRules->push('\'nullable\'');
                $serverUpdateRules->push('\'nullable\'');
            }

            if ($column['name'] === 'email') {
                $serverStoreRules->push('\'email\'');
                $serverUpdateRules->push('\'email\'');
                $frontendRules->push('email');
            }

            if ($column['name'] === 'password') {
                $serverStoreRules->push('\'confirmed\'');
                $serverUpdateRules->push('\'confirmed\'');
                $frontendRules->push('confirmed:password');

                $serverStoreRules->push('\'min:7\'');
                $serverUpdateRules->push('\'min:7\'');
                $frontendRules->push('min:7');

                $serverStoreRules->push('\'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9]).*$/\'');
                $serverUpdateRules->push('\'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9]).*$/\'');
                //TODO not working, need fixing
    //                $frontendRules->push(''regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[!$#%]).*$/g'');
            }

            if ($column['unique'] || $column['name'] === 'slug') {
                if ($column['type'] === 'json') {
                    $storeRule = 'Rule::unique(\'' . $tableName . '\', \'' . $column['name'] . '->\'.$locale)';
                    $updateRule = 'Rule::unique(\'' . $tableName . '\', \'' . $column['name'] . '->\'.$locale)->ignore($this->' . $modelVariableName . '->getKey(), $this->' . $modelVariableName . '->getKeyName())';
                    if ($hasSoftDelete && $column['unique_deleted_at_condition']) {
                        $storeRule .= '->whereNull(\'deleted_at\')';
                        $updateRule .= '->whereNull(\'deleted_at\')';
                    }
                        $serverStoreRules->push($storeRule);
                        $serverUpdateRules->push($updateRule);
                } else {
                    $storeRule = 'Rule::unique(\'' . $tableName . '\', \'' . $column['name'] . '\')';
                    $updateRule = 'Rule::unique(\'' . $tableName . '\', \'' . $column['name'] . '\')->ignore($this->' . $modelVariableName . '->getKey(), $this->' . $modelVariableName . '->getKeyName())';
                    if ($hasSoftDelete && $column['unique_deleted_at_condition']) {
                        $storeRule .= '->whereNull(\'deleted_at\')';
                        $updateRule .= '->whereNull(\'deleted_at\')';
                    }
                    $serverStoreRules->push($storeRule);
                    $serverUpdateRules->push($updateRule);
                }
            }

            switch ($column['type']) {
                case 'datetime':
                    $serverStoreRules->push('\'date\'');
                    $serverUpdateRules->push('\'date\'');
                    $frontendRules->push('date_format:yyyy-MM-dd HH:mm:ss');

                    break;
                case 'date':
                    $serverStoreRules->push('\'date\'');
                    $serverUpdateRules->push('\'date\'');
                    $frontendRules->push('date_format:yyyy-MM-dd HH:mm:ss');

                    break;
                case 'time':
                    $serverStoreRules->push('\'date_format:H:i:s\'');
                    $serverUpdateRules->push('\'date_format:H:i:s\'');
                    $frontendRules->push('date_format:HH:mm:ss');

                    break;
                case 'integer':
                    $serverStoreRules->push('\'integer\'');
                    $serverUpdateRules->push('\'integer\'');
                    $frontendRules->push('integer');

                    break;
                case 'tinyInteger':
                    $serverStoreRules->push('\'integer\'');
                    $serverUpdateRules->push('\'integer\'');
                    $frontendRules->push('integer');

                    break;
                case 'smallInteger':
                    $serverStoreRules->push('\'integer\'');
                    $serverUpdateRules->push('\'integer\'');
                    $frontendRules->push('integer');

                    break;
                case 'mediumInteger':
                    $serverStoreRules->push('\'integer\'');
                    $serverUpdateRules->push('\'integer\'');
                    $frontendRules->push('integer');

                    break;
                case 'bigInteger':
                    $serverStoreRules->push('\'integer\'');
                    $serverUpdateRules->push('\'integer\'');
                    $frontendRules->push('integer');

                    break;
                case 'unsignedInteger':
                    $serverStoreRules->push('\'integer\'');
                    $serverUpdateRules->push('\'integer\'');
                    $frontendRules->push('integer');

                    break;
                case 'unsignedTinyInteger':
                    $serverStoreRules->push('\'integer\'');
                    $serverUpdateRules->push('\'integer\'');
                    $frontendRules->push('integer');

                    break;
                case 'unsignedSmallInteger':
                    $serverStoreRules->push('\'integer\'');
                    $serverUpdateRules->push('\'integer\'');
                    $frontendRules->push('integer');

                    break;
                case 'unsignedMediumInteger':
                    $serverStoreRules->push('\'integer\'');
                    $serverUpdateRules->push('\'integer\'');
                    $frontendRules->push('integer');

                    break;
                case 'unsignedBigInteger':
                    $serverStoreRules->push('\'integer\'');
                    $serverUpdateRules->push('\'integer\'');
                    $frontendRules->push('integer');

                    break;
                case 'boolean':
                    $serverStoreRules->push('\'boolean\'');
                    $serverUpdateRules->push('\'boolean\'');
                    $frontendRules->push('');

                    break;
                case 'float':
                    $serverStoreRules->push('\'numeric\'');
                    $serverUpdateRules->push('\'numeric\'');
                    $frontendRules->push('decimal');

                    break;
                case 'decimal':
                    $serverStoreRules->push('\'numeric\'');
                    $serverUpdateRules->push('\'numeric\'');
                    // FIXME?? I'm not sure about this one
                    $frontendRules->push('decimal');

                    break;
                case 'string':
                    $serverStoreRules->push('\'string\'');
                    $serverUpdateRules->push('\'string\'');

                    break;
                case 'text':
                    $serverStoreRules->push('\'string\'');
                    $serverUpdateRules->push('\'string\'');

                    break;
                default:
                    $serverStoreRules->push('\'string\'');
                    $serverUpdateRules->push('\'string\'');
            }

                return [
                'name' => $column['name'],
                'type' => $column['type'],
                'serverStoreRules' => $serverStoreRules->toArray(),
                'serverUpdateRules' => $serverUpdateRules->toArray(),
                'frontendRules' => $frontendRules->toArray(),
                ];
        });
    }
}
