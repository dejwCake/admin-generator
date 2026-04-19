<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns\ColumnCollection;

use Brackets\AdminGenerator\Dtos\Columns\Column;
use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class GetFrontendValidationRulesTest extends TestCase
{
    public function testReturnsMappedRules(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('title', frontendRules: new Collection(['required', 'string'])),
            self::makeColumn('views', majorType: 'integer', frontendRules: new Collection(['integer'])),
            self::makeColumn('slug', frontendRules: new Collection()),
        ]);

        $result = $collection->getFrontendValidationRules();

        self::assertSame([
            'title' => "'required|string'",
            'views' => "'integer'",
        ], $result);
    }

    public function testExcludesColumnsWithNullRule(): void
    {
        $collection = new ColumnCollection([
            self::makeColumn('title', frontendRules: new Collection()),
        ]);

        self::assertSame([], $collection->getFrontendValidationRules());
    }

    private static function makeColumn(
        string $name,
        string $majorType = 'string',
        ?Collection $frontendRules = null,
    ): Column {
        return new Column(
            name: $name,
            majorType: $majorType,
            phpType: 'string',
            faker: 'word()',
            required: false,
            defaultTranslation: $name,
            isForeignKey: false,
            priority: null,
            serverStoreRules: new Collection(),
            serverUpdateRules: new Collection(),
            frontendRules: $frontendRules ?? new Collection(),
        );
    }
}
