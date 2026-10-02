<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Relations\RelationCollection;

use Brackets\AdminGenerator\Dtos\Relations\BelongsToMany;
use Brackets\AdminGenerator\Dtos\Relations\HasMany;
use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
use PHPUnit\Framework\TestCase;

final class HasManyTest extends TestCase
{
    public function testPushHasManyAndGetHasMany(): void
    {
        $collection = new RelationCollection();
        $relation = self::makeHasMany('comments', 'post_id');

        $collection->pushHasMany($relation);

        $result = $collection->getHasMany();
        self::assertCount(1, $result);
        self::assertSame($relation, $result->get('comments'));
    }

    public function testHasHasManyReturnsTrueWhenNotEmpty(): void
    {
        $collection = new RelationCollection();
        $collection->pushHasMany(self::makeHasMany('comments', 'post_id'));

        self::assertTrue($collection->hasHasMany());
    }

    public function testHasHasManyReturnsFalseWhenEmpty(): void
    {
        $collection = new RelationCollection();

        self::assertFalse($collection->hasHasMany());
    }

    public function testGetHasManyWithoutBelongsToManyConflictExcludesTheConflictingRelation(): void
    {
        $collection = new RelationCollection();
        $collection->pushBelongsToMany(self::makeBelongsToMany('tags'));
        $collection->pushHasMany(self::makeHasMany('comments'));
        $collection->pushHasMany(self::makeHasMany('tags', relationMethodName: 'tags'));

        $result = $collection->getHasManyWithoutBelongsToManyConflict();

        self::assertCount(1, $result);
        self::assertTrue($result->has('comments'));
        self::assertFalse($result->has('tags'));
    }

    public function testGetHasManyWithoutBelongsToManyConflictKeepsEverythingWhenNothingConflicts(): void
    {
        $collection = new RelationCollection();
        $collection->pushBelongsToMany(self::makeBelongsToMany('tags'));
        $collection->pushHasMany(self::makeHasMany('comments'));

        self::assertCount(1, $collection->getHasManyWithoutBelongsToManyConflict());
        self::assertTrue($collection->hasHasManyWithoutBelongsToManyConflict());
    }

    public function testHasHasManyWithoutBelongsToManyConflictIsFalseWhenEveryRelationConflicts(): void
    {
        $collection = new RelationCollection();
        $collection->pushBelongsToMany(self::makeBelongsToMany('tags'));
        $collection->pushHasMany(self::makeHasMany('tags', relationMethodName: 'tags'));

        self::assertTrue($collection->hasHasMany());
        self::assertFalse($collection->hasHasManyWithoutBelongsToManyConflict());
    }

    private static function makeHasMany(
        string $relatedTable = 'comments',
        string $foreignKeyColumn = 'post_id',
        string $relationMethodName = 'comments',
    ): HasMany {
        return new HasMany(
            relatedTable: $relatedTable,
            relatedModel: 'App\\Models\\Comment',
            relatedModelName: 'Comment',
            relationMethodName: $relationMethodName,
            foreignKeyColumn: $foreignKeyColumn,
        );
    }

    private static function makeBelongsToMany(string $relatedTable = 'tags'): BelongsToMany
    {
        return new BelongsToMany(
            relatedTable: $relatedTable,
            relatedModel: 'App\\Models\\Tag',
            relatedModelName: 'Tag',
            relatedLabel: 'name',
            relationTable: 'post_tag',
            relationMethodName: $relatedTable,
            relationTranslationKey: $relatedTable,
            relationTranslationValue: 'Tags',
            optionsAttributeName: 'tagOptions',
            optionsPropName: 'tagOptions',
            foreignKey: 'post_id',
            relatedKey: 'tag_id',
        );
    }
}
