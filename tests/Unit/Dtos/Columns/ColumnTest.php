<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns;

use Brackets\AdminGenerator\Dtos\Columns\Column;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class ColumnTest extends TestCase
{
    // -------------------------------------------------------------------------
    // getFrontendValidationRule
    // -------------------------------------------------------------------------

    public function testGetFrontendValidationRuleReturnsNullWhenCollectionIsEmpty(): void
    {
        $column = self::makeColumn(frontendRules: new Collection());

        self::assertNull($column->getFrontendValidationRule());
    }

    public function testGetFrontendValidationRuleReturnsNullWhenAllEntriesAreFalsy(): void
    {
        $column = self::makeColumn(frontendRules: new Collection(['', '', '']));

        self::assertNull($column->getFrontendValidationRule());
    }

    public function testGetFrontendValidationRuleReturnsSingleQuotedPipedString(): void
    {
        $column = self::makeColumn(frontendRules: new Collection(['required', 'integer']));

        self::assertSame("'required|integer'", $column->getFrontendValidationRule());
    }

    public function testGetFrontendValidationRuleReturnsSingleQuotedSingleRule(): void
    {
        $column = self::makeColumn(frontendRules: new Collection(['required']));

        self::assertSame("'required'", $column->getFrontendValidationRule());
    }

    // -------------------------------------------------------------------------
    // withPriority
    // -------------------------------------------------------------------------

    public function testWithPriorityReturnsNewColumnWithUpdatedPriority(): void
    {
        $original = self::makeColumn(priority: null);
        $updated = $original->withPriority(3);

        self::assertSame(3, $updated->priority);
    }

    public function testWithPriorityPreservesAllOtherFields(): void
    {
        $storeRules = new Collection();
        $updateRules = new Collection();
        $frontendRules = new Collection(['required']);

        $original = self::makeColumn(
            name: 'my_field',
            majorType: 'string',
            phpType: 'string',
            faker: 'word()',
            required: true,
            defaultTranslation: 'My Field',
            isForeignKey: false,
            priority: null,
            serverStoreRules: $storeRules,
            serverUpdateRules: $updateRules,
            frontendRules: $frontendRules,
        );

        $updated = $original->withPriority(5);

        self::assertSame('my_field', $updated->name);
        self::assertSame('string', $updated->majorType);
        self::assertSame('string', $updated->phpType);
        self::assertSame('word()', $updated->faker);
        self::assertTrue($updated->required);
        self::assertSame('My Field', $updated->defaultTranslation);
        self::assertFalse($updated->isForeignKey);
        self::assertSame(5, $updated->priority);
        self::assertSame($storeRules, $updated->serverStoreRules);
        self::assertSame($updateRules, $updated->serverUpdateRules);
        self::assertSame($frontendRules, $updated->frontendRules);
    }

    public function testWithPriorityOriginalIsUnchanged(): void
    {
        $original = self::makeColumn(priority: 1);
        $original->withPriority(99);

        self::assertSame(1, $original->priority);
    }

    public function testWithPriorityAcceptsNull(): void
    {
        $original = self::makeColumn(priority: 5);
        $updated = $original->withPriority(null);

        self::assertNull($updated->priority);
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private static function makeColumn(
        string $name = 'title',
        string $majorType = 'string',
        string $phpType = 'string',
        string $faker = 'word()',
        bool $required = false,
        string $defaultTranslation = 'Title',
        bool $isForeignKey = false,
        ?int $priority = null,
        ?Collection $serverStoreRules = null,
        ?Collection $serverUpdateRules = null,
        ?Collection $frontendRules = null,
    ): Column {
        return new Column(
            name: $name,
            majorType: $majorType,
            phpType: $phpType,
            faker: $faker,
            required: $required,
            defaultTranslation: $defaultTranslation,
            isForeignKey: $isForeignKey,
            priority: $priority,
            serverStoreRules: $serverStoreRules ?? new Collection(),
            serverUpdateRules: $serverUpdateRules ?? new Collection(),
            frontendRules: $frontendRules ?? new Collection(),
        );
    }
}
