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
            ->filter(static fn (array $column): bool => !in_array($column['name'], $ignoredColumns, true));
    }
}
