<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Builders\ServerUpdateRulesBuilder;

use Brackets\AdminGenerator\Builders\ServerUpdateRulesBuilder;
use Brackets\AdminGenerator\Dtos\Columns\Rules\UniqueRule;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class UniqueTest extends TestCase
{
    private readonly ServerUpdateRulesBuilder $serverUpdateRulesBuilder;

    protected function setUp(): void
    {
        $this->serverUpdateRulesBuilder = new ServerUpdateRulesBuilder();
    }

    public function testUniqueColumnAddsUniqueRuleWithModelVariableName(): void
    {
        $rules = $this->build(name: 'code', unique: true, tableName: 'products', modelVariableName: 'product');

        $uniqueRule = $rules->first(static fn ($rule): bool => $rule instanceof UniqueRule);

        self::assertNotNull($uniqueRule);
        self::assertStringContainsString("Rule::unique('products', 'code')", (string) $uniqueRule);
        self::assertStringContainsString(
            '->ignore($this->product->getKey(), $this->product->getKeyName())',
            (string) $uniqueRule,
        );
    }

    public function testUniqueRuleHasIgnoreClause(): void
    {
        $rules = $this->build(name: 'slug', tableName: 'posts', modelVariableName: 'post');

        $uniqueRule = $rules->first(static fn ($rule): bool => $rule instanceof UniqueRule);

        self::assertNotNull($uniqueRule);
        self::assertStringContainsString('->ignore(', (string) $uniqueRule);
    }

    public function testUniqueRuleForJsonTypeHasLocaleEnabled(): void
    {
        $rules = $this->build(name: 'title', type: 'json', unique: true, tableName: 'posts', modelVariableName: 'post');

        $uniqueRule = $rules->first(static fn ($rule): bool => $rule instanceof UniqueRule);

        self::assertNotNull($uniqueRule);
        self::assertStringContainsString("'title'->", (string) $uniqueRule);
    }

    public function testUniqueRuleForJsonbTypeHasLocaleEnabled(): void
    {
        $rules = $this->build(name: 'name', type: 'jsonb', unique: true, tableName: 'tags', modelVariableName: 'tag');

        $uniqueRule = $rules->first(static fn ($rule): bool => $rule instanceof UniqueRule);

        self::assertNotNull($uniqueRule);
        self::assertStringContainsString("'name'->", (string) $uniqueRule);
    }

    public function testSlugNameForcesUniqueRuleWithoutUniqueFlag(): void
    {
        $rules = $this->build(name: 'slug', unique: false, tableName: 'articles', modelVariableName: 'article');

        $uniqueRule = $rules->first(static fn ($rule): bool => $rule instanceof UniqueRule);

        self::assertNotNull($uniqueRule);
    }

    public function testExcludeDeletedAtAddsWhereNullClause(): void
    {
        $rules = $this->build(
            name: 'code',
            unique: true,
            tableName: 'items',
            excludeDeletedAt: true,
            modelVariableName: 'item',
        );

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
        string $modelVariableName = 'article',
    ): Collection {
        return $this->serverUpdateRulesBuilder->build(
            name: $name,
            type: $type,
            majorType: 'string',
            required: false,
            unique: $unique,
            tableName: $tableName,
            excludeDeletedAt: $excludeDeletedAt,
            modelVariableName: $modelVariableName,
        );
    }
}
