<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Traits;

use Brackets\AdminGenerator\Dtos\Columns\Column;
use Illuminate\Support\Collection;

trait Columns
{
    protected function getRelatedLabelColumn(string $tableName, string $modelVariableName): string
    {
        $columns = $this->columnCollectionBuilder->build($tableName, $modelVariableName);
        $preferredLabels = ['title', 'name', 'first_name', 'email'];

        foreach ($preferredLabels as $label) {
            if ($columns->has($label)) {
                return $label;
            }
        }

        $firstString = $columns->first(
            static fn (Column $column): bool => $column->majorType === 'string',
        );

        return $firstString?->name ?? 'id';
    }

    /** @return Collection<string|int, array<string, string|array<string>>> */
    protected function getVisibleColumns(
        string $tableName,
        string $modelVariableName,
        array $ignoredColumns = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'last_login_at'],
    ): Collection {
        $columns = $this->columnCollectionBuilder->build($tableName, $modelVariableName)
            ->toLegacyCollection();

        return $columns
            ->filter(static fn (array $column): bool => !in_array($column['name'], $ignoredColumns, true))
            ->map(fn (array $column): array => [
                'name' => $column['name'],
                'type' => $column['type'],
                'majorType' => $column['majorType'],
                'serverStoreRules' => $column['serverStoreRules'],
                'serverUpdateRules' => $column['serverUpdateRules'],
                'frontendRules' => $this->getFrontendRules($column)->toArray(),
            ]);
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

    /**
     * @param array<string, string|bool> $column
     */
    protected function getFrontendRulesByRequire(array $column, Collection $frontendRules): Collection
    {
        if ($column['required'] && $column['majorType'] !== 'bool' && $column['name'] !== 'password') {
            $frontendRules->push('required');
        }

        return $frontendRules;
    }

    protected function getFrontendRulesByName(string $name, Collection $frontendRules): Collection
    {
        if ($name === 'email') {
            $frontendRules->push('email');
        }

        if ($name === 'password') {
            $frontendRules->push('confirmed:password');
            $frontendRules->push('min:8');
            //TODO not working, need fixing
//            $frontendRules->push('regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[!$#%]).*$/g');
        }

        return $frontendRules;
    }

    protected function getFrontendRulesByType(string $type, Collection $frontendRules): Collection
    {
        $majorType = $this->getMajorTypeFromType($type);

        $rule = match ($majorType) {
            'datetime',
            'date' => 'date_format:yyyy-MM-dd HH:mm:ss',
            'time' => 'date_format:HH:mm:ss',
            'integer' => 'integer',
            'float' => 'numeric',
            'bool' => '',
            default => null,
        };

        if ($rule !== null) {
            $frontendRules->push($rule);
        }

        return $frontendRules;
    }

    private function getMajorTypeFromType(string $type): string
    {
        return match ($type) {
            'datetime',
            'timestamp',
            'timestamptz' => 'datetime',
            'date' => 'date',
            'timetz',
            'time' => 'time',
            'int2',
            'smallint',
            'smallinteger',
            'unsignedsmallint',
            'unsignedsmallinteger',
            'int3',
            'mediumint',
            'mediuminteger',
            'unsignedmediumint',
            'unsignedmediuminteger',
            'int4',
            'int',
            'integer',
            'unsignedinteger',
            'int8',
            'bigint',
            'biginteger',
            'unsignedbigint',
            'unsignedbiginteger' => 'integer',
            'decimal',
            'dec',
            'number',
            'numeric',
            'float',
            'float8',
            'double',
            'real',
            'float4' => 'float',
            'bit',
            'int1',
            'tinyint',
            'tinyinteger',
            'unsignedtinyinteger',
            'bool',
            'boolean' => 'bool',
            'longtext',
            'json',
            'jsonb' => 'json',
            'char',
            'enum',
            'inet4',
            'inet6',
            'tinytext',
            'varchar',
            'uuid',
            'string' => 'string',
            default => 'text',
        };
    }
}
