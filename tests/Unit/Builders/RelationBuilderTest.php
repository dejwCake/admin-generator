<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Builders;

use Brackets\AdminGenerator\Builders\BelongsToBuilder;
use Brackets\AdminGenerator\Builders\BelongsToManyBuilder;
use Brackets\AdminGenerator\Builders\HasManyBuilder;
use Brackets\AdminGenerator\Builders\RelationBuilder;
use Illuminate\Database\Schema\Builder as Schema;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

final class RelationBuilderTest extends TestCase
{
    public function testConstructorDoesNotPerformDatabaseIo(): void
    {
        $schema = $this->createMock(Schema::class);
        $schema->expects(self::never())->method(self::anything());

        new RelationBuilder(
            $schema,
            self::stubFinal(BelongsToManyBuilder::class),
            self::stubFinal(BelongsToBuilder::class),
            self::stubFinal(HasManyBuilder::class),
        );
    }

    public function testConstructorSucceedsEvenWhenSchemaWouldThrow(): void
    {
        $schema = $this->createMock(Schema::class);
        $schema->method('getTables')->willThrowException(new RuntimeException('no DB connection'));

        new RelationBuilder(
            $schema,
            self::stubFinal(BelongsToManyBuilder::class),
            self::stubFinal(BelongsToBuilder::class),
            self::stubFinal(HasManyBuilder::class),
        );

        self::assertTrue(true, 'construction did not trigger schema access');
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private static function stubFinal(string $class): object
    {
        return (new ReflectionClass($class))->newInstanceWithoutConstructor();
    }
}
