<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Relations;

use Illuminate\Support\Collection;

final class RelationCollection
{
    /** @var Collection<string, BelongsTo> */
    private Collection $belongsTo;

    /** @var Collection<string, BelongsToMany> */
    private Collection $belongsToMany;

    /** @var Collection<string, HasMany> */
    private Collection $hasMany;

    public function __construct()
    {
        $this->belongsTo = new Collection();
        $this->belongsToMany = new Collection();
        $this->hasMany = new Collection();
    }

    public function pushBelongsToMany(BelongsToMany $belongsToManyRelation): void
    {
        $this->belongsToMany->put($belongsToManyRelation->relatedTable, $belongsToManyRelation);
    }

    public function pushBelongsTo(BelongsTo $belongsToRelation): void
    {
        $this->belongsTo->put($belongsToRelation->foreignKeyColumn, $belongsToRelation);
    }

    public function pushHasMany(HasMany $hasManyRelation): void
    {
        $this->hasMany->put($hasManyRelation->relatedTable, $hasManyRelation);
    }

    public function hasBelongsTo(): bool
    {
        return $this->belongsTo->isNotEmpty();
    }

    /** @return Collection<string, BelongsTo> */
    public function getBelongsTo(): Collection
    {
        return $this->belongsTo;
    }

    public function hasBelongsToByColumn(string $columnName): bool
    {
        return $this->belongsTo->contains(
            static fn (BelongsTo $belongsTo) => $belongsTo->foreignKeyColumn === $columnName,
        );
    }

    public function getBelongsToByColumn(string $columnName): ?BelongsTo
    {
        return $this->belongsTo->get($columnName);
    }

    public function hasBelongsToMany(): bool
    {
        return $this->belongsToMany->isNotEmpty();
    }

    /** @return Collection<string, BelongsToMany> */
    public function getBelongsToMany(): Collection
    {
        return $this->belongsToMany;
    }

    public function hasBelongsToManyWithoutRelatedTable(string $relatedTable): bool
    {
        return $this->getBelongsToManyWithoutRelatedTable($relatedTable)->isNotEmpty();
    }

    /** @return Collection<string, BelongsToMany> */
    public function getBelongsToManyWithoutRelatedTable(string $relatedTable): Collection
    {
        return $this->belongsToMany->reject(
            static fn (BelongsToMany $belongsToMany) => $belongsToMany->relatedTable === $relatedTable,
        );
    }

    public function hasRelatedTableInBelongsToMany(string $relatedTable): bool
    {
        return $this->belongsToMany->contains(
            static fn (BelongsToMany $belongsToMany) => $belongsToMany->relatedTable === $relatedTable,
        );
    }

    public function hasRelationMethodNameInBelongsToMany(string $relationMethodName): bool
    {
        return $this->belongsToMany->contains(
            static fn (BelongsToMany $belongsToMany) => $belongsToMany->relationMethodName === $relationMethodName,
        );
    }

    public function isPivotTable(string $tableName): bool
    {
        return $this->belongsToMany->contains(
            static fn (BelongsToMany $belongsToMany) => $belongsToMany->relationTable === $tableName,
        );
    }

    public function hasHasMany(): bool
    {
        return $this->hasMany->isNotEmpty();
    }

    /** @return Collection<string, HasMany> */
    public function getHasMany(): Collection
    {
        return $this->hasMany;
    }
}
