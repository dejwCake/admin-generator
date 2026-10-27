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
    private const int LOWEST_PRIORITY = 10;

    private ColumnCollection $columnCollection;

    public function __construct(private Schema $schema, private ColumnBuilder $columnBuilder,)
    {
        $this->columnCollection = new ColumnCollection();
    }

    /**
     * @param array<int, string>|null $translatable
     */
    public function build(
        string $tableName,
        ?string $modelVariableName = null,
        ?array $translatable = null,
    ): ColumnCollection {
        $modelVariableName ??= Naming::variableName($tableName);
        $this->columnCollection = new ColumnCollection();
        $columns = new Collection($this->schema->getColumns($tableName));
        $indexes = new Collection($this->schema->getIndexes($tableName));

        $hasSoftDelete = $columns->contains(static fn (array $column): bool => $column['name'] === 'deleted_at');

        $columns->each(
            function (array $column) use (
                $indexes,
                $hasSoftDelete,
                $tableName,
                $modelVariableName,
                $translatable,
            ): void {
                $this->columnCollection->push(
                    $this->columnBuilder->build(
                        $column['name'],
                        $column['type_name'],
                        $column['nullable'],
                        $tableName,
                        $indexes,
                        $hasSoftDelete,
                        $modelVariableName,
                        $translatable,
                    ),
                );
            },
        );

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
                && !in_array($column->name, ColumnCollection::SETUP_ONLY_COLUMNS, true)
                && !$column->isWysiwyg,
        );

        $priorities = $this->resolvePriorities($indexEligible);

        $reassigned = new ColumnCollection();

        foreach ($this->columnCollection as $column) {
            $reassigned->push(
                array_key_exists($column->name, $priorities)
                    ? $column->withPriority($priorities[$column->name])
                    : $column,
            );
        }

        $this->columnCollection = $reassigned;
    }

    /**
     * @return array<string, int>
     */
    private function resolvePriorities(ColumnCollection $indexEligible): array
    {
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

        $priorities = [];
        $uuidNames = [];

        foreach ($indexEligible as $column) {
            if ($column->priority !== null) {
                $priorities[$column->name] = $priorityMap[$column->priority];
            } elseif (in_array($column->name, ColumnCollection::UUID_COLUMNS, true)) {
                $uuidNames[] = $column->name;
            } else {
                $priorities[$column->name] = min($nextPriority, self::LOWEST_PRIORITY);
                $nextPriority++;
            }
        }

        // The uuid ranks after every other column, so it is hidden first, yet a narrow table still shows it.
        foreach ($uuidNames as $uuidName) {
            $priorities[$uuidName] = min($nextPriority, self::LOWEST_PRIORITY);
            $nextPriority++;
        }

        return $priorities;
    }
}
