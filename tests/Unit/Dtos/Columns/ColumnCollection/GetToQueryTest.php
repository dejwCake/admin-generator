<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit\Dtos\Columns\ColumnCollection;

use Brackets\AdminGenerator\Dtos\Columns\Column;
use Brackets\AdminGenerator\Dtos\Columns\ColumnCollection;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class GetToQueryTest extends TestCase
{
    // Branch A: neither created_by nor updated_by
    // Rejects: password, remember_token, slug, created_at, updated_at, deleted_at, text-major

    public function testBranchARejectsDefaultHaystackAndTextMajor(): void
    {
        $col = new ColumnCollection([
            self::makeColumn('title'),
            self::makeColumn('slug'),
            self::makeColumn('password'),
            self::makeColumn('remember_token'),
            self::makeColumn('created_at', majorType: 'datetime'),
            self::makeColumn('updated_at', majorType: 'datetime'),
            self::makeColumn('deleted_at', majorType: 'datetime'),
            self::makeColumn('notes', majorType: 'text'),
        ]);

        $result = $col->getToQuery();
        $names = array_keys($result->toArray());

        self::assertSame(['title'], $names);
    }

    public function testBranchATextMajorAlwaysExcluded(): void
    {
        $col = new ColumnCollection([
            self::makeColumn('summary', majorType: 'text'),
            self::makeColumn('title'),
        ]);

        $result = $col->getToQuery();

        self::assertArrayNotHasKey('summary', $result->toArray());
        self::assertArrayHasKey('title', $result->toArray());
    }

    // Branch B: both created_by_admin_user_id and updated_by_admin_user_id present
    // Rejects: password, remember_token, slug, deleted_at (keeps created_at, updated_at)

    public function testBranchBKeepsCreatedAtAndUpdatedAt(): void
    {
        $col = new ColumnCollection([
            self::makeColumn('title'),
            self::makeColumn('created_by_admin_user_id', majorType: 'integer'),
            self::makeColumn('updated_by_admin_user_id', majorType: 'integer'),
            self::makeColumn('created_at', majorType: 'datetime'),
            self::makeColumn('updated_at', majorType: 'datetime'),
            self::makeColumn('deleted_at', majorType: 'datetime'),
            self::makeColumn('slug'),
            self::makeColumn('password'),
            self::makeColumn('remember_token'),
        ]);

        $result = $col->getToQuery();
        $names = array_keys($result->toArray());

        self::assertContains('title', $names);
        self::assertContains('created_by_admin_user_id', $names);
        self::assertContains('updated_by_admin_user_id', $names);
        self::assertContains('created_at', $names);
        self::assertContains('updated_at', $names);
        self::assertNotContains('deleted_at', $names);
        self::assertNotContains('slug', $names);
        self::assertNotContains('password', $names);
        self::assertNotContains('remember_token', $names);
    }

    public function testBranchBTextMajorStillExcluded(): void
    {
        $col = new ColumnCollection([
            self::makeColumn('created_by_admin_user_id', majorType: 'integer'),
            self::makeColumn('updated_by_admin_user_id', majorType: 'integer'),
            self::makeColumn('notes', majorType: 'text'),
            self::makeColumn('title'),
        ]);

        $result = $col->getToQuery();

        self::assertArrayNotHasKey('notes', $result->toArray());
        self::assertArrayHasKey('title', $result->toArray());
    }

    // Branch C: only created_by_admin_user_id present
    // Rejects: password, remember_token, slug, updated_at, deleted_at (keeps created_at)

    public function testBranchCKeepsCreatedAtRejectsUpdatedAt(): void
    {
        $col = new ColumnCollection([
            self::makeColumn('title'),
            self::makeColumn('created_by_admin_user_id', majorType: 'integer'),
            self::makeColumn('created_at', majorType: 'datetime'),
            self::makeColumn('updated_at', majorType: 'datetime'),
            self::makeColumn('deleted_at', majorType: 'datetime'),
            self::makeColumn('slug'),
        ]);

        $result = $col->getToQuery();
        $names = array_keys($result->toArray());

        self::assertContains('title', $names);
        self::assertContains('created_by_admin_user_id', $names);
        self::assertContains('created_at', $names);
        self::assertNotContains('updated_at', $names);
        self::assertNotContains('deleted_at', $names);
        self::assertNotContains('slug', $names);
    }

    // Branch D: only updated_by_admin_user_id present
    // Rejects: password, remember_token, slug, created_at, deleted_at (keeps updated_at)

    public function testBranchDKeepsUpdatedAtRejectsCreatedAt(): void
    {
        $col = new ColumnCollection([
            self::makeColumn('title'),
            self::makeColumn('updated_by_admin_user_id', majorType: 'integer'),
            self::makeColumn('created_at', majorType: 'datetime'),
            self::makeColumn('updated_at', majorType: 'datetime'),
            self::makeColumn('deleted_at', majorType: 'datetime'),
            self::makeColumn('slug'),
        ]);

        $result = $col->getToQuery();
        $names = array_keys($result->toArray());

        self::assertContains('title', $names);
        self::assertContains('updated_by_admin_user_id', $names);
        self::assertContains('updated_at', $names);
        self::assertNotContains('created_at', $names);
        self::assertNotContains('deleted_at', $names);
        self::assertNotContains('slug', $names);
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private static function makeColumn(
        string $name = 'title',
        string $majorType = 'string',
        bool $isForeignKey = false,
        ?int $priority = null,
    ): Column {
        return new Column(
            name: $name,
            majorType: $majorType,
            phpType: 'string',
            faker: 'word()',
            required: false,
            defaultTranslation: $name,
            isForeignKey: $isForeignKey,
            priority: $priority,
            serverStoreRules: new Collection(),
            serverUpdateRules: new Collection(),
            frontendRules: new Collection(),
        );
    }
}
