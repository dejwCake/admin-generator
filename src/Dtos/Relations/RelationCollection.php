<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Relations;

use Illuminate\Support\Collection;

final class RelationCollection
{
    /** @var Collection<string, BelongsToRelation> */
    private Collection $belongsTo;

    /** @var Collection<string, BelongsToManyRelation> */
    private Collection $belongsToMany;

    public function __construct()
    {
        $this->belongsTo = new Collection();
        $this->belongsToMany = new Collection();
    }

    public function pushBelongsToMany(BelongsToManyRelation $belongsToManyRelation): void
    {
        $this->belongsToMany->put($belongsToManyRelation->relatedTable, $belongsToManyRelation);
    }

    public function pushBelongsTo(BelongsToRelation $belongsToRelation): void
    {
        $this->belongsTo->put($belongsToRelation->relatedTable, $belongsToRelation);
    }

    public function hasBelongsToMany(): bool
    {
        return $this->belongsToMany->isNotEmpty();
    }

    /** @return Collection<string, BelongsToManyRelation> */
    public function getBelongsToMany(): Collection
    {
        return $this->belongsToMany;
    }

    /** @return Collection<int, string> */
    public function getBelongsToManyTables(): Collection
    {
        return $this->belongsToMany->pluck('relatedTable');
    }

    /** @deprecated just for compatibility with old code */
    public function toLegacyArray(): array
    {
        return [
            'belongsToMany' => $this->belongsToMany
                ->map(static fn (BelongsToManyRelation $relation) => $relation->toLegacyArray()),
        ];
    }
}
