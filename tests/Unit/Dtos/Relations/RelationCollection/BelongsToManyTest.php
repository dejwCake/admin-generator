<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Relations\RelationCollection;

use Brackets\AdminGenerator\Dtos\Relations\BelongsToMany;
use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
use PHPUnit\Framework\TestCase;

final class BelongsToManyTest extends TestCase
{
    public function testPushBelongsToManyAndGetBelongsToMany(): void
    {
        $collection = new RelationCollection();
        $relation = self::makeBelongsToMany('tags', 'tag_post');

        $collection->pushBelongsToMany($relation);

        $result = $collection->getBelongsToMany();
        self::assertCount(1, $result);
        self::assertSame($relation, $result->get('tags'));
    }

    public function testHasBelongsToManyReturnsTrueWhenNotEmpty(): void
    {
        $collection = new RelationCollection();
        $collection->pushBelongsToMany(self::makeBelongsToMany('tags', 'tag_post'));

        self::assertTrue($collection->hasBelongsToMany());
    }

    public function testHasBelongsToManyReturnsFalseWhenEmpty(): void
    {
        $collection = new RelationCollection();

        self::assertFalse($collection->hasBelongsToMany());
    }

    public function testGetBelongsToManyWithoutRelatedTableExcludesGivenTable(): void
    {
        $collection = new RelationCollection();
        $collection->pushBelongsToMany(self::makeBelongsToMany('tags', 'tag_post'));
        $collection->pushBelongsToMany(self::makeBelongsToMany('categories', 'category_post'));

        $result = $collection->getBelongsToManyWithoutRelatedTable('tags');

        self::assertCount(1, $result);
        self::assertNull($result->get('tags'));
        self::assertNotNull($result->get('categories'));
    }

    public function testHasBelongsToManyWithoutRelatedTableReturnsTrueWhenOthersExist(): void
    {
        $collection = new RelationCollection();
        $collection->pushBelongsToMany(self::makeBelongsToMany('tags', 'tag_post'));
        $collection->pushBelongsToMany(self::makeBelongsToMany('categories', 'category_post'));

        self::assertTrue($collection->hasBelongsToManyWithoutRelatedTable('tags'));
    }

    public function testHasBelongsToManyWithoutRelatedTableReturnsFalseWhenOnlyOneExists(): void
    {
        $collection = new RelationCollection();
        $collection->pushBelongsToMany(self::makeBelongsToMany('tags', 'tag_post'));

        self::assertFalse($collection->hasBelongsToManyWithoutRelatedTable('tags'));
    }

    public function testHasRelatedTableInBelongsToManyReturnsTrueWhenExists(): void
    {
        $collection = new RelationCollection();
        $collection->pushBelongsToMany(self::makeBelongsToMany('tags', 'tag_post'));

        self::assertTrue($collection->hasRelatedTableInBelongsToMany('tags'));
    }

    public function testHasRelatedTableInBelongsToManyReturnsFalseWhenMissing(): void
    {
        $collection = new RelationCollection();
        $collection->pushBelongsToMany(self::makeBelongsToMany('tags', 'tag_post'));

        self::assertFalse($collection->hasRelatedTableInBelongsToMany('categories'));
    }

    public function testHasRelationMethodNameInBelongsToManyReturnsTrueWhenExists(): void
    {
        $collection = new RelationCollection();
        $collection->pushBelongsToMany(self::makeBelongsToMany('tags', 'tag_post', relationMethodName: 'tags'));

        self::assertTrue($collection->hasRelationMethodNameInBelongsToMany('tags'));
    }

    public function testHasRelationMethodNameInBelongsToManyReturnsFalseWhenMissing(): void
    {
        $collection = new RelationCollection();
        $collection->pushBelongsToMany(self::makeBelongsToMany('tags', 'tag_post', relationMethodName: 'tags'));

        self::assertFalse($collection->hasRelationMethodNameInBelongsToMany('categories'));
    }

    public function testIsPivotTableReturnsTrueWhenRelationTableMatches(): void
    {
        $collection = new RelationCollection();
        $collection->pushBelongsToMany(self::makeBelongsToMany('tags', 'tag_post'));

        self::assertTrue($collection->isPivotTable('tag_post'));
    }

    public function testIsPivotTableReturnsFalseWhenNotMatching(): void
    {
        $collection = new RelationCollection();
        $collection->pushBelongsToMany(self::makeBelongsToMany('tags', 'tag_post'));

        self::assertFalse($collection->isPivotTable('category_post'));
    }

    private static function makeBelongsToMany(
        string $relatedTable = 'tags',
        string $relationTable = 'tag_post',
        string $relationMethodName = 'tags',
    ): BelongsToMany {
        return new BelongsToMany(
            relatedTable: $relatedTable,
            relatedModel: 'App\\Models\\Tag',
            relatedModelName: 'Tag',
            relatedLabel: 'name',
            relationTable: $relationTable,
            relationMethodName: $relationMethodName,
            relationTranslationKey: 'tags',
            relationTranslationValue: 'Tags',
            optionsAttributeName: 'tagOptions',
            optionsPropName: 'tagOptions',
            foreignKey: 'post_id',
            relatedKey: 'tag_id',
        );
    }
}
