<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Columns;

use Illuminate\Support\Collection;

final class ColumnCollection
{
    /** @var Collection<string, Column> */
    private Collection $columns;

    public function __construct(array $columns = [])
    {
        $this->columns = (new Collection($columns))
            ->filter(fn ($column) => $column instanceof Column)
            ->keyBy(fn (Column $column) => $column->name);
    }

    public function push(Column $column): void
    {
        $this->columns->push($column);
    }

    /** @deprecated */
    public function toLegacyCollection(): Collection {
        return $this->columns->map(fn (Column $column) => $column->toLegacyArray());
    }
}
