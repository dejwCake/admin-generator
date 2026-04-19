<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Unit;

use Brackets\AdminGenerator\Naming;
use PHPUnit\Framework\TestCase;

final class NamingTest extends TestCase
{
    public function testModelNamePluralSnake(): void
    {
        self::assertSame('Category', Naming::modelName('categories'));
    }

    public function testModelNameSingleWord(): void
    {
        self::assertSame('Post', Naming::modelName('posts'));
    }

    public function testModelNameMultiWordSnake(): void
    {
        self::assertSame('AdminUser', Naming::modelName('admin_users'));
    }

    public function testVariableNamePluralSnake(): void
    {
        self::assertSame('category', Naming::variableName('categories'));
    }

    public function testVariableNameSingleWord(): void
    {
        self::assertSame('post', Naming::variableName('posts'));
    }

    public function testVariableNameMultiWordSnake(): void
    {
        self::assertSame('adminUser', Naming::variableName('admin_users'));
    }
}
