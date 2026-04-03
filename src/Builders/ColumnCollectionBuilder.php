<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Illuminate\Database\Schema\Builder as Schema;
use Illuminate\Support\Collection;

final class ColumnCollectionBuilder
{
    private ColumnCollection $columnCollection;

    public function __construct(private Schema $schema, private ColumnBuilder $columnBuilder,)
    {
        $this->columnCollection = new ColumnCollection();
    }

    public function build(string $tableName, string $modelVariableName): ColumnCollection
    {
        $this->columnCollection = new ColumnCollection();
        $columns = new Collection($this->schema->getColumns($tableName));
        $indexes = new Collection($this->schema->getIndexes($tableName));

        $hasSoftDelete = $columns->contains(static fn (array $column): bool => $column['name'] === 'deleted_at');

        $columns->each(function (array $column) use ($indexes, $hasSoftDelete, $tableName, $modelVariableName): void {
            $this->columnCollection->push(
                $this->columnBuilder->build(
                    $column['name'],
                    $column['type_name'],
                    $column['nullable'],
                    $tableName,
                    $indexes,
                    $hasSoftDelete,
                    $modelVariableName,
                ),
            );
        });

        return $this->columnCollection;
    }
}
