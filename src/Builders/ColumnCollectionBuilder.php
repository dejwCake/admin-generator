<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Builders;

use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Illuminate\Database\Schema\Builder as Schema;
use Illuminate\Support\Collection;

final readonly class ColumnCollectionBuilder
{
    public function __construct(
        private Schema $schema,
        private ColumnBuilder $columnBuilder,
    ) {
    }

    public function build(string $tableName) {
        $columnCollection = new ColumnCollection();

        $indexes = new Collection($this->schema->getIndexes($tableName));

        return new Collection($this->schema->getColumns($tableName))
            ->each(function (array $column) use ($indexes, $columnCollection): void {
                $columnCollection->push(
                    $this->columnBuilder->build(
                        $indexes,
                        $column['name'],
                        $column['type_name'],
                        $column['nullable'],
                    )
                );
            }
        );
    }
}
