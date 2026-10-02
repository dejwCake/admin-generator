<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns;

use Brackets\AdminGenerator\Builders\ColumnBuilder;
use Brackets\AdminGenerator\Dtos\Columns\Column;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
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
    // stateMethodName / negatedStateMethodName
    // -------------------------------------------------------------------------

    #[DataProvider('getStateMethodNameCases')]
    public function testStateMethodNameIsCamelCase(string $columnName, string $expected): void
    {
        self::assertSame($expected, self::makeColumn(name: $columnName)->stateMethodName());
    }

    #[DataProvider('getNegatedStateMethodNameCases')]
    public function testNegatedStateMethodNameIsCamelCase(string $columnName, string $expected): void
    {
        self::assertSame($expected, self::makeColumn(name: $columnName)->negatedStateMethodName());
    }

    public static function getStateMethodNameCases(): iterable
    {
        yield 'single word' => ['enabled', 'enabled'];
        yield 'snake case' => ['up_event_enabled', 'upEventEnabled'];
        yield 'is prefix is kept' => ['is_url_owner', 'isUrlOwner'];
    }

    public static function getNegatedStateMethodNameCases(): iterable
    {
        yield 'single word' => ['enabled', 'notEnabled'];
        yield 'snake case' => ['up_event_enabled', 'notUpEventEnabled'];
        yield 'is prefix negates inside the phrase' => ['is_url_owner', 'isNotUrlOwner'];
        yield 'is on its own word' => ['island', 'notIsland'];
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
            isTranslatable: $majorType === 'json',
            isWysiwyg: in_array($name, ColumnBuilder::WYSIWYG_COLUMN_NAMES, true) && in_array(
                $majorType,
                ['text', 'json'],
                true,
            ),
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
