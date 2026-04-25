<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Builders\ServerStoreRulesBuilder;

use Brackets\AdminGenerator\Builders\ServerStoreRulesBuilder;
use Brackets\AdminGenerator\Dtos\Columns\Rules\UniqueRule;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class UniqueTest extends TestCase
{
    private readonly ServerStoreRulesBuilder $serverStoreRulesBuilder;

    protected function setUp(): void
    {
        $this->serverStoreRulesBuilder = new ServerStoreRulesBuilder();
    }

    public function testUniqueColumnAddsUniqueRule(): void
    {
        $rules = $this->build(name: 'code', unique: true, tableName: 'products');

        $uniqueRule = $rules->first(static fn ($rule): bool => $rule instanceof UniqueRule);

        self::assertNotNull($uniqueRule);
        self::assertStringContainsString("Rule::unique('products', 'code')", (string) $uniqueRule);
    }

    public function testUniqueRuleForNonJsonTypeHasLocaleDisabled(): void
    {
        $rules = $this->build(name: 'code', type: 'varchar', unique: true, tableName: 'items');

        $uniqueRule = $rules->first(static fn ($rule): bool => $rule instanceof UniqueRule);

        self::assertNotNull($uniqueRule);
        self::assertStringContainsString("'code'", (string) $uniqueRule);
        self::assertStringNotContainsString('->', (string) $uniqueRule);
    }

    public function testUniqueRuleForJsonTypeHasLocaleEnabled(): void
    {
        $rules = $this->build(name: 'title', type: 'json', unique: true, tableName: 'posts');

        $uniqueRule = $rules->first(static fn ($rule): bool => $rule instanceof UniqueRule);

        self::assertNotNull($uniqueRule);
        self::assertStringContainsString("'title'->", (string) $uniqueRule);
    }

    public function testUniqueRuleForJsonbTypeHasLocaleEnabled(): void
    {
        $rules = $this->build(name: 'name', type: 'jsonb', unique: true, tableName: 'tags');

        $uniqueRule = $rules->first(static fn ($rule): bool => $rule instanceof UniqueRule);

        self::assertNotNull($uniqueRule);
        self::assertStringContainsString("'name'->", (string) $uniqueRule);
    }

    public function testSlugNameForcesUniqueRuleWithoutUniqueFlag(): void
    {
        $rules = $this->build(name: 'slug', unique: false, tableName: 'articles');

        $uniqueRule = $rules->first(static fn ($rule): bool => $rule instanceof UniqueRule);

        self::assertNotNull($uniqueRule);
    }

    public function testExcludeDeletedAtAddsWhereNullClause(): void
    {
        $rules = $this->build(name: 'code', unique: true, tableName: 'items', excludeDeletedAt: true);

        $uniqueRule = $rules->first(static fn ($rule): bool => $rule instanceof UniqueRule);

        self::assertNotNull($uniqueRule);
        self::assertStringContainsString("->whereNull('deleted_at')", (string) $uniqueRule);
    }

    public function testNoUniqueRuleWhenNotUniqueAndNotSlug(): void
    {
        $rules = $this->build(name: 'title', unique: false);

        $uniqueRule = $rules->first(static fn ($rule): bool => $rule instanceof UniqueRule);

        self::assertNull($uniqueRule);
    }

    private function build(
        string $name = 'title',
        string $type = 'varchar',
        bool $unique = false,
        string $tableName = 'articles',
        bool $excludeDeletedAt = false,
    ): Collection {
        return $this->serverStoreRulesBuilder->build(
            name: $name,
            type: $type,
            majorType: 'string',
            required: false,
            unique: $unique,
            tableName: $tableName,
            excludeDeletedAt: $excludeDeletedAt,
        );
    }
}
