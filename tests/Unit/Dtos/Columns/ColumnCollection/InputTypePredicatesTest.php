<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns\ColumnCollection;

use Brackets\AdminGenerator\Dtos\Columns\Column;
use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class InputTypePredicatesTest extends TestCase
{
    public function testHasWysiwygReturnsTrueForTextMajorTypeWithWysiwygName(): void
    {
        $collection = new ColumnCollection([self::makeColumn('perex', majorType: 'text')]);

        self::assertTrue($collection->hasWysiwyg());
    }

    public function testHasWysiwygReturnsTrueForJsonMajorTypeWithWysiwygName(): void
    {
        $collection = new ColumnCollection([self::makeColumn('description', majorType: 'json')]);

        self::assertTrue($collection->hasWysiwyg());
    }

    public function testHasWysiwygReturnsFalseForTextWithNonWysiwygName(): void
    {
        $collection = new ColumnCollection([self::makeColumn('notes', majorType: 'text')]);

        self::assertFalse($collection->hasWysiwyg());
    }

    public function testHasTextareaReturnsTrueForTextMajorTypeWithNonWysiwygName(): void
    {
        $collection = new ColumnCollection([self::makeColumn('notes', majorType: 'text')]);

        self::assertTrue($collection->hasTextarea());
    }

    public function testHasTextareaReturnsFalseForTextWithWysiwygName(): void
    {
        $collection = new ColumnCollection([self::makeColumn('perex', majorType: 'text')]);

        self::assertFalse($collection->hasTextarea());
    }

    public function testHasLocalizedInputReturnsTrueForJsonWithNonWysiwygName(): void
    {
        $collection = new ColumnCollection([self::makeColumn('data', majorType: 'json')]);

        self::assertTrue($collection->hasLocalizedInput());
    }

    public function testHasLocalizedInputReturnsFalseForJsonWithWysiwygName(): void
    {
        $collection = new ColumnCollection([self::makeColumn('description', majorType: 'json')]);

        self::assertFalse($collection->hasLocalizedInput());
    }

    public function testHasLocalizedWysiwygReturnsTrueForJsonWithWysiwygName(): void
    {
        $collection = new ColumnCollection([self::makeColumn('body', majorType: 'json')]);

        self::assertTrue($collection->hasLocalizedWysiwyg());
    }

    public function testHasLocalizedWysiwygReturnsFalseForJsonWithNonWysiwygName(): void
    {
        $collection = new ColumnCollection([self::makeColumn('data', majorType: 'json')]);

        self::assertFalse($collection->hasLocalizedWysiwyg());
    }

    private static function makeColumn(string $name, string $majorType = 'string'): Column
    {
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
            frontendRules: new Collection(),
        );
    }
}
