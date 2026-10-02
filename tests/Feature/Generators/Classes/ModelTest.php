<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators\Classes;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\Attributes\DataProvider;

use function file_get_contents;

final class ModelTest extends TestCase
{
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerateClass(array $arguments, string $expectedFilePath): void
    {
        $filePath = $this->app->basePath($expectedFilePath);

        self::assertFileDoesNotExist($filePath);

        $this->artisan('admin:generate:model', $arguments);

        self::assertFileExists($filePath);
        self::assertMatchesFileSnapshot($filePath);
    }

    public function testGeneratorWithForceShouldOverwriteClass(): void
    {
        $filePath = $this->app->basePath('app/Models/Category.php');

        $this->artisan('admin:generate:model', ['table_name' => 'categories']);
        self::assertFileExists($filePath);

        $this->artisan('admin:generate:model', [
            'table_name' => 'categories',
            '--force' => true,
        ]);
        self::assertFileExists($filePath);
    }

    public function testGeneratorShouldSeparateConsecutiveHasManyRelationsWithOneBlankLine(): void
    {
        $schemaBuilder = $this->app['db']->connection()->getSchemaBuilder();
        foreach (['comments', 'ratings'] as $childTable) {
            $schemaBuilder->create($childTable, static function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('post_id');
                $table->foreign('post_id')->references('id')->on('posts');
            });
        }

        $this->artisan('admin:generate:model', ['table_name' => 'posts']);

        $contents = (string) file_get_contents($this->app->basePath('app/Models/Post.php'));

        self::assertStringContainsString('public function comments(): HasMany', $contents);
        self::assertStringContainsString('public function ratings(): HasMany', $contents);
        // A blank line is "\n\n"; three in a row is the double blank line the sniff rejects.
        self::assertStringNotContainsString("\n\n\n", $contents);
    }

    public static function getCases(): iterable
    {
        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFilePath' => 'app/Models/Category.php',
        ];

        yield 'categories with translatable text' => [
            'arguments' => ['table_name' => 'categories', '--translatable' => 'text'],
            'expectedFilePath' => 'app/Models/Category.php',
        ];

        yield 'categories with class_name Billing\\Category' => [
            'arguments' => ['table_name' => 'categories', 'class_name' => 'Billing\\Category'],
            'expectedFilePath' => 'app/Models/Billing/Category.php',
        ];

        yield 'categories with class_name App\\Billing\\Category' => [
            'arguments' => ['table_name' => 'categories', 'class_name' => 'App\\Billing\\Category'],
            'expectedFilePath' => 'app/Billing/Category.php',
        ];

        yield 'categories with belongs-to-many posts' => [
            'arguments' => ['table_name' => 'categories', '--belongs-to-many' => 'posts'],
            'expectedFilePath' => 'app/Models/Category.php',
        ];

        yield 'categories with media' => [
            'arguments' => ['table_name' => 'categories', '--media' => ['gallery:image:public:5000']],
            'expectedFilePath' => 'app/Models/Category.php',
        ];

        yield 'categories with media and max file size' => [
            'arguments' => ['table_name' => 'categories', '--media' => ['gallery:image:public:5000:25']],
            'expectedFilePath' => 'app/Models/Category.php',
        ];

        yield 'categories with template admin-user' => [
            'arguments' => ['table_name' => 'categories', '--template' => 'admin-user'],
            'expectedFilePath' => 'app/Models/Category.php',
        ];

        yield 'categories with template user' => [
            'arguments' => ['table_name' => 'categories', '--template' => 'user'],
            'expectedFilePath' => 'app/Models/Category.php',
        ];

        yield 'posts default' => [
            'arguments' => ['table_name' => 'posts'],
            'expectedFilePath' => 'app/Models/Post.php',
        ];

        yield 'posts with class_name Feed\\Article' => [
            'arguments' => ['table_name' => 'posts', 'class_name' => 'Feed\\Article'],
            'expectedFilePath' => 'app/Models/Feed/Article.php',
        ];

        yield 'posts with class_name App\\Feed\\Post' => [
            'arguments' => ['table_name' => 'posts', 'class_name' => 'App\\Feed\\Post'],
            'expectedFilePath' => 'app/Feed/Post.php',
        ];

        yield 'posts with belongs-to-many categories' => [
            'arguments' => ['table_name' => 'posts', '--belongs-to-many' => 'categories'],
            'expectedFilePath' => 'app/Models/Post.php',
        ];
    }
}
