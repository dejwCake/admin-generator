<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Builders\ColumnBuilder;

use Brackets\AdminGenerator\Builders\ColumnBuilder;
use Brackets\AdminGenerator\Builders\FrontendRulesBuilder;
use Brackets\AdminGenerator\Builders\ServerStoreRulesBuilder;
use Brackets\AdminGenerator\Builders\ServerUpdateRulesBuilder;
use Brackets\AdminGenerator\Dtos\Columns\Column;
use Brackets\AdminGenerator\Dtos\Columns\Rules\UniqueRule;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class UniqueIndexTest extends TestCase
{
    private readonly ColumnBuilder $columnBuilder;

    protected function setUp(): void
    {
        $this->columnBuilder = new ColumnBuilder(
            new ServerStoreRulesBuilder(),
            new ServerUpdateRulesBuilder(),
            new FrontendRulesBuilder(),
        );
    }

    public function testUniqueIndexCausesUniqueRuleInServerStoreRules(): void
    {
        $indexes = new Collection([
            [
                'name' => 'items_code_unique',
                'columns' => ['code'],
                'unique' => true,
                'primary' => false,
            ],
        ]);

        $column = $this->buildColumn(name: 'code', indexes: $indexes);

        $hasUniqueRule = $column->serverStoreRules->contains(
            static fn ($rule): bool => $rule instanceof UniqueRule,
        );

        self::assertTrue($hasUniqueRule);
    }

    public function testPrimaryIndexDoesNotCauseUniqueRule(): void
    {
        $indexes = new Collection([
            [
                'name' => 'items_code_primary',
                'columns' => ['code'],
                'unique' => true,
                'primary' => true,
            ],
        ]);

        $column = $this->buildColumn(name: 'code', indexes: $indexes);

        $hasUniqueRule = $column->serverStoreRules->contains(
            static fn ($rule): bool => $rule instanceof UniqueRule,
        );

        self::assertFalse($hasUniqueRule);
    }

    public function testNullDeletedAtIndexWithSoftDeleteAddsWhereNullClause(): void
    {
        $indexes = new Collection([
            [
                'name' => 'items_code_null_deleted_at_unique',
                'columns' => ['code'],
                'unique' => true,
                'primary' => false,
            ],
        ]);

        $column = $this->buildColumn(name: 'code', indexes: $indexes, hasSoftDelete: true);

        $uniqueRule = $column->serverStoreRules->first(
            static fn ($rule): bool => $rule instanceof UniqueRule,
        );

        self::assertNotNull($uniqueRule);
        self::assertStringContainsString("->whereNull('deleted_at')", (string) $uniqueRule);
    }

    public function testNullDeletedAtIndexWithoutSoftDeleteDoesNotAddWhereNullClause(): void
    {
        $indexes = new Collection([
            [
                'name' => 'items_code_null_deleted_at_unique',
                'columns' => ['code'],
                'unique' => true,
                'primary' => false,
            ],
        ]);

        $column = $this->buildColumn(name: 'code', indexes: $indexes, hasSoftDelete: false);

        $uniqueRule = $column->serverStoreRules->first(
            static fn ($rule): bool => $rule instanceof UniqueRule,
        );

        self::assertNotNull($uniqueRule);
        self::assertStringNotContainsString("->whereNull('deleted_at')", (string) $uniqueRule);
    }

    private function buildColumn(
        string $name = 'title',
        ?Collection $indexes = null,
        bool $hasSoftDelete = false,
    ): Column {
        return $this->columnBuilder->build(
            name: $name,
            type: 'varchar',
            nullable: true,
            tableName: 'articles',
            indexes: $indexes ?? new Collection(),
            hasSoftDelete: $hasSoftDelete,
            modelVariableName: 'article',
        );
    }
}
