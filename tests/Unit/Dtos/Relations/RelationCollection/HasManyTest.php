<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Relations\RelationCollection;

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

    private static function makeHasMany(
        string $relatedTable = 'comments',
        string $foreignKeyColumn = 'post_id',
    ): HasMany {
        return new HasMany(
            relatedTable: $relatedTable,
            relatedModel: 'App\\Models\\Comment',
            relatedModelName: 'Comment',
            relationMethodName: 'comments',
            foreignKeyColumn: $foreignKeyColumn,
        );
    }
}
