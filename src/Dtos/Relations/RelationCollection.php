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

    public function __construct()
    {
        $this->belongsTo = new Collection();
        $this->belongsToMany = new Collection();
    }

    public function pushBelongsToMany(BelongsToMany $belongsToManyRelation): void
    {
        $this->belongsToMany->put($belongsToManyRelation->relatedTable, $belongsToManyRelation);
    }

    public function pushBelongsTo(BelongsTo $belongsToRelation): void
    {
        $this->belongsTo->put($belongsToRelation->relatedTable, $belongsToRelation);
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

    public function hasBelongsToManyWithoutRoles(): bool
    {
        return $this->getBelongsToManyWithoutRoles()->isNotEmpty();
    }

    /** @return Collection<string, BelongsToMany> */
    public function getBelongsToManyWithoutRoles(): Collection
    {
        return $this->belongsToMany->reject(
            static fn (BelongsToMany $belongsToMany) => $belongsToMany->relatedTable === 'roles',
        );
    }

    public function hasRelatedTableInBelongsToMany(string $relatedTable): bool
    {
        return $this->belongsToMany->contains(
            static fn (BelongsToMany $belongsToMany) => $belongsToMany->relatedTable === $relatedTable,
        );
    }
}
