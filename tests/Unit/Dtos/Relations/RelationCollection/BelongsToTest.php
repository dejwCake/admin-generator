<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Relations\RelationCollection;

use Brackets\AdminGenerator\Dtos\Relations\BelongsTo;
use Brackets\AdminGenerator\Dtos\Relations\RelationCollection;
use PHPUnit\Framework\TestCase;

final class BelongsToTest extends TestCase
{
    public function testPushBelongsToAndGetBelongsTo(): void
    {
        $collection = new RelationCollection();
        $relation = self::makeBelongsTo('user_id', 'users');

        $collection->pushBelongsTo($relation);

        $result = $collection->getBelongsTo();
        self::assertCount(1, $result);
        self::assertSame($relation, $result->get('user_id'));
    }

    public function testHasBelongsToReturnsTrueWhenNotEmpty(): void
    {
        $collection = new RelationCollection();
        $collection->pushBelongsTo(self::makeBelongsTo('user_id', 'users'));

        self::assertTrue($collection->hasBelongsTo());
    }

    public function testHasBelongsToReturnsFalseWhenEmpty(): void
    {
        $collection = new RelationCollection();

        self::assertFalse($collection->hasBelongsTo());
    }

    public function testHasBelongsToByColumnReturnsTrueWhenColumnExists(): void
    {
        $collection = new RelationCollection();
        $collection->pushBelongsTo(self::makeBelongsTo('category_id', 'categories'));

        self::assertTrue($collection->hasBelongsToByColumn('category_id'));
    }

    public function testHasBelongsToByColumnReturnsFalseWhenColumnMissing(): void
    {
        $collection = new RelationCollection();
        $collection->pushBelongsTo(self::makeBelongsTo('category_id', 'categories'));

        self::assertFalse($collection->hasBelongsToByColumn('user_id'));
    }

    public function testGetBelongsToByColumnReturnsRelationWhenExists(): void
    {
        $collection = new RelationCollection();
        $relation = self::makeBelongsTo('category_id', 'categories');
        $collection->pushBelongsTo($relation);

        self::assertSame($relation, $collection->getBelongsToByColumn('category_id'));
    }

    public function testGetBelongsToByColumnReturnsNullWhenMissing(): void
    {
        $collection = new RelationCollection();

        self::assertNull($collection->getBelongsToByColumn('missing_id'));
    }

    private static function makeBelongsTo(
        string $foreignKeyColumn = 'user_id',
        string $relatedTable = 'users',
    ): BelongsTo {
        return new BelongsTo(
            foreignKeyColumn: $foreignKeyColumn,
            relatedTable: $relatedTable,
            relatedModel: 'App\\Models\\User',
            relatedModelName: 'User',
            relatedLabel: 'name',
            relationMethodName: 'user',
            optionsAttributeName: 'userOptions',
            optionsPropName: 'userOptions',
        );
    }
}
