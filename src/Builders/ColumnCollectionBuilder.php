<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Brackets\AdminGenerator\Dtos\Columns\Column;
use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Brackets\AdminGenerator\Naming;
use Illuminate\Database\Schema\Builder as Schema;
use Illuminate\Support\Collection;

final class ColumnCollectionBuilder
{
    private ColumnCollection $columnCollection;

    public function __construct(private Schema $schema, private ColumnBuilder $columnBuilder,)
    {
        $this->columnCollection = new ColumnCollection();
    }

    public function build(string $tableName, ?string $modelVariableName = null): ColumnCollection
    {
        $modelVariableName ??= Naming::variableName($tableName);
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

        $this->assignPriorities();

        return $this->columnCollection;
    }

    private function assignPriorities(): void
    {
        $indexEligible = $this->columnCollection->filter(
            static fn (Column $column): bool => $column->majorType !== 'text'
                && !in_array(
                    $column->name,
                    ['password', 'remember_token', 'slug', 'created_at', 'updated_at', 'deleted_at'],
                    true,
                )
                && !($column->majorType === 'json' && in_array(
                    $column->name,
                    ColumnCollection::WYSIWYG_COLUMN_NAMES,
                    true,
                )),
        );

        $fixedPriorities = $indexEligible
            ->pluck('priority')
            ->filter(static fn (?int $priority): bool => $priority !== null)
            ->unique()
            ->sort()
            ->values();

        $priorityMap = $fixedPriorities
            ->mapWithKeys(static fn (int $priority, int $index): array => [$priority => $index])
            ->all();

        $nextPriority = count($priorityMap);

        $indexNames = $indexEligible->pluck('name')->all();

        $reassigned = new ColumnCollection();

        foreach ($this->columnCollection as $column) {
            if (!in_array($column->name, $indexNames, true)) {
                $reassigned->push($column);
            } elseif ($column->priority !== null) {
                $reassigned->push($column->withPriority($priorityMap[$column->priority]));
            } else {
                $reassigned->push($column->withPriority(min($nextPriority, 10)));
                $nextPriority++;
            }
        }

        $this->columnCollection = $reassigned;
    }
}
