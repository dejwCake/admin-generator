<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Columns;

use ArrayIterator;
use Closure;
use Countable;
use Illuminate\Support\Collection;
use IteratorAggregate;
use Traversable;

/** @implements IteratorAggregate<string, Column> */
final class ColumnCollection implements IteratorAggregate, Countable
{
    /** @var Collection<string, Column> */
    private Collection $columns;

    public function __construct(array $columns = [])
    {
        $this->columns = (new Collection($columns))
            ->filter(static fn ($column) => $column instanceof Column)
            ->keyBy(static fn (Column $column) => $column->name);
    }

    public function push(Column $column): void
    {
        $this->columns->put($column->name, $column);
    }

    public function get(string $name): ?Column
    {
        return $this->columns->get($name);
    }

    public function has(string $name): bool
    {
        return $this->columns->has($name);
    }

    public function first(?Closure $callback = null): ?Column
    {
        return $this->columns->first($callback);
    }

    public function filter(Closure $callback): self
    {
        $filtered = new self();
        $filtered->columns = $this->columns->filter($callback);

        return $filtered;
    }

    public function contains(Closure $callback): bool
    {
        return $this->columns->contains($callback);
    }

    /** @return Collection<int|string, bool|Column|string|null> */
    public function map(Closure $callback): Collection
    {
        return $this->columns->map($callback);
    }

    /** @return Collection<int, string|bool> */
    public function pluck(string $property): Collection
    {
        return $this->columns->pluck($property);
    }

    public function count(): int
    {
        return $this->columns->count();
    }

    /** @return array<string, Column> */
    public function toArray(): array
    {
        return $this->columns->all();
    }

    /** @return Traversable<string, Column> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->columns->all());
    }

    /** @deprecated just for compatibility with old code */
    public function toLegacyCollection(): Collection
    {
        return $this->columns->map(static fn (Column $column) => $column->toLegacyArray());
    }
}
