<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class GenerateAdminUserProfileTest extends TestCase
{
    private const array PRE_EXISTING_APPEND_TARGETS = [
        'routes/admin.php',
    ];

    private const array COMMON_EXPECTED_FILES = [
        'app/Http/Controllers/Admin/ProfileController.php',
        'resources/views/admin/profile/edit-profile.blade.php',
        'resources/views/admin/profile/edit-password.blade.php',
        'resources/js/admin/profile-edit-profile/Form.vue',
        'resources/js/admin/profile-edit-password/Form.vue',
        'routes/admin.php',
    ];

    /**
     * @param array<string, string|bool> $arguments
     * @param list<string> $expectedFiles
     * @param list<string> $missingFiles
     */
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerate(array $arguments, array $expectedFiles, array $missingFiles,): void
    {
        foreach ($expectedFiles as $relativePath) {
            if (in_array($relativePath, self::PRE_EXISTING_APPEND_TARGETS, true)) {
                continue;
            }
            self::assertFileDoesNotExist($this->app->basePath($relativePath));
        }
        foreach ($missingFiles as $relativePath) {
            self::assertFileDoesNotExist($this->app->basePath($relativePath));
        }

        $this->artisan('admin:generate:admin-user:profile', $arguments);

        foreach ($expectedFiles as $relativePath) {
            $path = $this->app->basePath($relativePath);
            self::assertFileExists($path);
            self::assertMatchesFileSnapshot($path);
        }
        foreach ($missingFiles as $relativePath) {
            self::assertFileDoesNotExist($this->app->basePath($relativePath));
        }
    }

    public function testGeneratorWithForceShouldOverwriteFiles(): void
    {
        $filePaths = [
            'app/Http/Controllers/Admin/ProfileController.php',
            'resources/views/admin/profile/edit-profile.blade.php',
            'resources/views/admin/profile/edit-password.blade.php',
            'resources/js/admin/profile-edit-profile/Form.vue',
            'resources/js/admin/profile-edit-password/Form.vue',
        ];

        $this->artisan('admin:generate:admin-user:profile');

        foreach ($filePaths as $relativePath) {
            self::assertFileExists($this->app->basePath($relativePath));
        }

        $this->artisan('admin:generate:admin-user:profile', ['--force' => true]);

        foreach ($filePaths as $relativePath) {
            self::assertFileExists($this->app->basePath($relativePath));
        }
    }

    public static function getCases(): iterable
    {
        $common = self::COMMON_EXPECTED_FILES;
        $defaultController = 'app/Http/Controllers/Admin/ProfileController.php';

        yield 'default' => [
            'arguments' => [],
            'expectedFiles' => $common,
            'missingFiles' => [],
        ];

        yield 'with controller-name Auth\\ProfileController' => [
            'arguments' => ['--controller-name' => 'Auth\\ProfileController'],
            'expectedFiles' => self::replaceController(
                $common,
                'app/Http/Controllers/Admin/Auth/ProfileController.php',
            ),
            'missingFiles' => [$defaultController],
        ];

        yield 'with controller-name App\\Http\\Controllers\\Auth\\ProfileController' => [
            'arguments' => ['--controller-name' => 'App\\Http\\Controllers\\Auth\\ProfileController'],
            'expectedFiles' => self::replaceController($common, 'app/Http/Controllers/Auth/ProfileController.php'),
            'missingFiles' => [$defaultController],
        ];

        yield 'with controller-name Auth\\ProfileController and model-name App\\User' => [
            'arguments' => [
                '--controller-name' => 'Auth\\ProfileController',
                '--model-name' => 'App\\User',
            ],
            'expectedFiles' => self::replaceController(
                $common,
                'app/Http/Controllers/Admin/Auth/ProfileController.php',
            ),
            'missingFiles' => [$defaultController],
        ];

        yield 'with model-name App\\User' => [
            'arguments' => ['--model-name' => 'App\\User'],
            'expectedFiles' => $common,
            'missingFiles' => [],
        ];

        yield 'with model-name Auth\\User' => [
            'arguments' => ['--model-name' => 'Auth\\User'],
            'expectedFiles' => $common,
            'missingFiles' => [],
        ];

        yield 'with table-name users' => [
            'arguments' => ['table_name' => 'users'],
            'expectedFiles' => $common,
            'missingFiles' => [],
        ];
    }

    /**
     * @param array<int, string> $files
     * @return array<int, string>
     */
    private static function replaceController(array $files, string $newControllerPath): array
    {
        return array_values(array_map(
            static fn (string $path): string => str_starts_with($path, 'app/Http/Controllers/')
                ? $newControllerPath
                : $path,
            $files,
        ));
    }
}
