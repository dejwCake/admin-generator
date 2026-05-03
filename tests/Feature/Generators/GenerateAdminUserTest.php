<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class GenerateAdminUserTest extends TestCase
{
    private const array PRE_EXISTING_APPEND_TARGETS = [
        'routes/admin.php',
        'lang/en/admin.php',
    ];

    private const array COMMON_EXPECTED_FILES = [
        'app/Http/Controllers/Admin/AdminUsersController.php',
        'app/Http/Requests/Admin/AdminUser/IndexAdminUser.php',
        'app/Http/Requests/Admin/AdminUser/StoreAdminUser.php',
        'app/Http/Requests/Admin/AdminUser/UpdateAdminUser.php',
        'app/Http/Requests/Admin/AdminUser/DestroyAdminUser.php',
        'app/Http/Requests/Admin/AdminUser/ImpersonalLoginAdminUser.php',
        'resources/views/admin/admin-user/index.blade.php',
        'resources/views/admin/admin-user/create.blade.php',
        'resources/views/admin/admin-user/edit.blade.php',
        'resources/js/admin/admin-user/Listing.vue',
        'resources/js/admin/admin-user/Form.vue',
        'database/factories/AdminUserFactory.php',
        'routes/admin.php',
        'routes/admin/admin-users.php',
        'lang/en/admin.php',
    ];

    /**
     * @param array<string, string|bool|array<string>> $options
     * @param list<string> $expectedFiles
     * @param list<string> $missingFiles
     */
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerate(array $options, array $expectedFiles, array $missingFiles,): void
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

        $this->artisan('admin:generate:admin-user', $options);

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
            'app/Http/Controllers/Admin/AdminUsersController.php',
            'app/Http/Requests/Admin/AdminUser/IndexAdminUser.php',
            'app/Http/Requests/Admin/AdminUser/StoreAdminUser.php',
            'app/Http/Requests/Admin/AdminUser/UpdateAdminUser.php',
            'app/Http/Requests/Admin/AdminUser/DestroyAdminUser.php',
            'app/Http/Requests/Admin/AdminUser/BulkDestroyAdminUser.php',
            'app/Http/Requests/Admin/AdminUser/ImpersonalLoginAdminUser.php',
            'resources/views/admin/admin-user/index.blade.php',
            'resources/views/admin/admin-user/create.blade.php',
            'resources/views/admin/admin-user/edit.blade.php',
            'resources/js/admin/admin-user/Listing.vue',
            'resources/js/admin/admin-user/Form.vue',
            'database/factories/AdminUserFactory.php',
        ];

        $this->artisan('admin:generate:admin-user');

        foreach ($filePaths as $relativePath) {
            self::assertFileExists($this->app->basePath($relativePath));
        }

        $this->artisan('admin:generate:admin-user', ['--force' => true]);

        foreach ($filePaths as $relativePath) {
            self::assertFileExists($this->app->basePath($relativePath));
        }
    }

    public static function getCases(): iterable
    {
        $common = self::COMMON_EXPECTED_FILES;
        $defaultController = 'app/Http/Controllers/Admin/AdminUsersController.php';
        $bulkDestroy = 'app/Http/Requests/Admin/AdminUser/BulkDestroyAdminUser.php';
        $exportRequest = 'app/Http/Requests/Admin/AdminUser/ExportAdminUser.php';
        $exportClass = 'app/Exports/AdminUsersExport.php';

        yield 'default' => [
            'options' => [],
            'expectedFiles' => [...$common, $bulkDestroy],
            'missingFiles' => [$exportRequest, $exportClass],
        ];

        yield 'with export' => [
            'options' => ['--with-export' => true],
            'expectedFiles' => [...$common, $bulkDestroy, $exportRequest, $exportClass],
            'missingFiles' => [],
        ];

        yield 'without bulk' => [
            'options' => ['--without-bulk' => true],
            'expectedFiles' => $common,
            'missingFiles' => [$bulkDestroy, $exportRequest, $exportClass],
        ];

        yield 'with export without bulk' => [
            'options' => ['--with-export' => true, '--without-bulk' => true],
            'expectedFiles' => [...$common, $exportRequest, $exportClass],
            'missingFiles' => [$bulkDestroy],
        ];

        yield 'with media gallery' => [
            'options' => ['--media' => ['gallery:image:public:5000']],
            'expectedFiles' => [...$common, $bulkDestroy],
            'missingFiles' => [$exportRequest, $exportClass],
        ];

        $commonWithNamespacedController = self::replaceController(
            $common,
            'app/Http/Controllers/Admin/Auth/AdminUsersController.php',
        );

        yield 'with controller-name Auth\\AdminUsersController' => [
            'options' => ['--controller-name' => 'Auth\\AdminUsersController'],
            'expectedFiles' => [...$commonWithNamespacedController, $bulkDestroy],
            'missingFiles' => [$defaultController, $exportRequest, $exportClass],
        ];

        $commonWithOutsideController = self::replaceController(
            $common,
            'app/Http/Controllers/Auth/AdminUsersController.php',
        );

        yield 'with controller-name App\\Http\\Controllers\\Auth\\AdminUsersController' => [
            'options' => ['--controller-name' => 'App\\Http\\Controllers\\Auth\\AdminUsersController'],
            'expectedFiles' => [...$commonWithOutsideController, $bulkDestroy],
            'missingFiles' => [$defaultController, $exportRequest, $exportClass],
        ];

        $commonForAppUser = self::commonForModelBaseName('User');
        $commonForAppUserWithController = self::replaceController(
            $commonForAppUser,
            'app/Http/Controllers/Admin/Auth/UsersController.php',
        );

        yield 'with model-name App\\User, controller-name Auth\\UsersController, with-export' => [
            'options' => [
                '--model-name' => 'App\\User',
                '--controller-name' => 'Auth\\UsersController',
                '--with-export' => true,
            ],
            'expectedFiles' => [
                ...$commonForAppUserWithController,
                'app/Http/Requests/Admin/User/BulkDestroyUser.php',
                'app/Http/Requests/Admin/User/ExportUser.php',
                $exportClass,
            ],
            'missingFiles' => [
                $defaultController,
                ...self::defaultAdminUserPaths(),
            ],
        ];

        $commonForAuthUser = self::commonForSubNamespace('Auth\\User');

        yield 'with model-name Auth\\User' => [
            'options' => ['--model-name' => 'Auth\\User'],
            'expectedFiles' => [
                ...$commonForAuthUser,
                'app/Http/Requests/Admin/Auth/User/BulkDestroyUser.php',
            ],
            'missingFiles' => [
                ...self::defaultAdminUserPaths(),
                'app/Http/Requests/Admin/Auth/User/ExportUser.php',
                $exportClass,
            ],
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

    /**
     * Builds the COMMON file list for a flat model base name (e.g. "User"):
     * requests move to app/Http/Requests/Admin/User/, blade views to
     * resources/views/admin/user/, Vue files to resources/js/admin/user/,
     * factory to database/factories/UserFactory.php.
     * Controller path is unchanged (caller may pass through replaceController()).
     *
     * @return array<int, string>
     */
    private static function commonForModelBaseName(string $modelBaseName): array
    {
        $kebab = strtolower((string) preg_replace('/(?<!^)([A-Z])/', '-$1', $modelBaseName));
        $resource = strtolower(
            (string) preg_replace('/(?<!^)([A-Z])/', '-$1', $modelBaseName . 's'),
        );

        return [
            'app/Http/Controllers/Admin/AdminUsersController.php',
            sprintf('app/Http/Requests/Admin/%s/Index%s.php', $modelBaseName, $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/Store%s.php', $modelBaseName, $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/Update%s.php', $modelBaseName, $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/Destroy%s.php', $modelBaseName, $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/ImpersonalLogin%s.php', $modelBaseName, $modelBaseName),
            sprintf('resources/views/admin/%s/index.blade.php', $kebab),
            sprintf('resources/views/admin/%s/create.blade.php', $kebab),
            sprintf('resources/views/admin/%s/edit.blade.php', $kebab),
            sprintf('resources/js/admin/%s/Listing.vue', $kebab),
            sprintf('resources/js/admin/%s/Form.vue', $kebab),
            sprintf('database/factories/%sFactory.php', $modelBaseName),
            'routes/admin.php',
            sprintf('routes/admin/%s.php', $resource),
            'lang/en/admin.php',
        ];
    }

    /**
     * Builds the COMMON file list remapped to a sub-namespace (e.g. "Auth\\User"):
     * requests move to app/Http/Requests/Admin/Auth/User/, blade views to
     * resources/views/admin/auth/user/, Vue files to resources/js/admin/auth-user/,
     * factory to database/factories/UserFactory.php. Controller unchanged.
     *
     * @return array<int, string>
     */
    private static function commonForSubNamespace(string $subNamespace): array
    {
        $segments = explode('\\', $subNamespace);
        $nestedSegments = array_map(
            static fn (string $part): string => lcfirst($part),
            $segments,
        );
        $requestsDir = implode('/', $nestedSegments);
        $bladeDir = strtolower(implode('/', $nestedSegments));
        $jsDir = strtolower(implode('-', $nestedSegments));
        $modelBaseName = (string) end($segments);

        $resourceSegments = $segments;
        array_pop($resourceSegments);
        $resourceSegments[] = $modelBaseName . 's';
        $resource = strtolower(
            (string) preg_replace('/(?<!^)([A-Z])/', '-$1', implode('', $resourceSegments)),
        );

        return [
            'app/Http/Controllers/Admin/AdminUsersController.php',
            sprintf('app/Http/Requests/Admin/%s/Index%s.php', self::ucPath($requestsDir), $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/Store%s.php', self::ucPath($requestsDir), $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/Update%s.php', self::ucPath($requestsDir), $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/Destroy%s.php', self::ucPath($requestsDir), $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/ImpersonalLogin%s.php', self::ucPath($requestsDir), $modelBaseName),
            sprintf('resources/views/admin/%s/index.blade.php', $bladeDir),
            sprintf('resources/views/admin/%s/create.blade.php', $bladeDir),
            sprintf('resources/views/admin/%s/edit.blade.php', $bladeDir),
            sprintf('resources/js/admin/%s/Listing.vue', $jsDir),
            sprintf('resources/js/admin/%s/Form.vue', $jsDir),
            sprintf('database/factories/%sFactory.php', $modelBaseName),
            'routes/admin.php',
            sprintf('routes/admin/%s.php', $resource),
            'lang/en/admin.php',
        ];
    }

    /**
     * Default-namespace paths (flat AdminUser/) that must NOT exist when a
     * custom --model-name is used.
     *
     * @return array<int, string>
     */
    private static function defaultAdminUserPaths(): array
    {
        return [
            'app/Http/Requests/Admin/AdminUser/IndexAdminUser.php',
            'app/Http/Requests/Admin/AdminUser/StoreAdminUser.php',
            'app/Http/Requests/Admin/AdminUser/UpdateAdminUser.php',
            'app/Http/Requests/Admin/AdminUser/DestroyAdminUser.php',
            'app/Http/Requests/Admin/AdminUser/BulkDestroyAdminUser.php',
            'app/Http/Requests/Admin/AdminUser/ImpersonalLoginAdminUser.php',
            'resources/views/admin/admin-user/index.blade.php',
            'resources/views/admin/admin-user/create.blade.php',
            'resources/views/admin/admin-user/edit.blade.php',
            'resources/js/admin/admin-user/Listing.vue',
            'resources/js/admin/admin-user/Form.vue',
            'database/factories/AdminUserFactory.php',
        ];
    }

    private static function ucPath(string $path): string
    {
        return implode('/', array_map(
            static fn (string $part): string => ucfirst($part),
            explode('/', $path),
        ));
    }
}
