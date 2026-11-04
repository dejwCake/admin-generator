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
use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * @return array<string, array{array<int, string>}>
     */
    public static function softDeleteAwareIndexColumns(): array
    {
        return [
            'deleted_at column' => [['code', 'deleted_at']],
            'deleted_unique column' => [['code', 'deleted_unique']],
        ];
    }

    /**
     * @param array<int, string> $columns
     */
    #[DataProvider('softDeleteAwareIndexColumns')]
    public function testSoftDeleteAwareIndexColumnWithSoftDeleteAddsWhereNullClause(array $columns): void
    {
        $indexes = new Collection([
            [
                'name' => 'items_code_unique',
                'columns' => $columns,
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

    public function testCompositeIndexDoesNotMakeTheLeadingColumnUnique(): void
    {
        $column = $this->buildColumn(name: 'team_id', indexes: $this->compositeIndex(['team_id', 'user_id']));

        $hasUniqueRule = $column->serverStoreRules->contains(
            static fn ($rule): bool => $rule instanceof UniqueRule,
        );

        self::assertFalse($hasUniqueRule);
    }

    public function testCompositeIndexScopesTheUniqueRuleOfTheLastColumn(): void
    {
        $column = $this->buildColumn(name: 'user_id', indexes: $this->compositeIndex(['team_id', 'user_id']));

        $storeRule = $column->serverStoreRules->first(static fn ($rule): bool => $rule instanceof UniqueRule);
        $updateRule = $column->serverUpdateRules->first(static fn ($rule): bool => $rule instanceof UniqueRule);

        self::assertNotNull($storeRule);
        self::assertStringContainsString("->where('team_id', \$this->input('team_id'))", (string) $storeRule);
        self::assertNotNull($updateRule);
        self::assertStringContainsString(
            "->where('team_id', \$this->input('team_id', \$this->article->team_id))",
            (string) $updateRule,
        );
    }

    public function testCompositeIndexWithDeletedUniqueScopesOnlyByKeyColumns(): void
    {
        $column = $this->buildColumn(
            name: 'user_id',
            indexes: $this->compositeIndex(['team_id', 'user_id', 'deleted_unique']),
            hasSoftDelete: true,
        );

        $storeRule = (string) $column->serverStoreRules->first(static fn ($rule): bool => $rule instanceof UniqueRule);

        self::assertStringContainsString("->where('team_id', \$this->input('team_id'))", $storeRule);
        self::assertStringContainsString("->whereNull('deleted_at')", $storeRule);
        self::assertStringNotContainsString('deleted_unique', $storeRule);
    }

    /**
     * @param array<int, string> $columns
     */
    private function compositeIndex(array $columns): Collection
    {
        return new Collection([
            [
                'name' => 'items_composite_unique',
                'columns' => $columns,
                'unique' => true,
                'primary' => false,
            ],
        ]);
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
