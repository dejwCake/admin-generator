<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class GenerateUserTest extends TestCase
{
    private const array PRE_EXISTING_APPEND_TARGETS = [
        'routes/admin.php',
        'lang/en/admin.php',
    ];

    private const array COMMON_EXPECTED_FILES = [
        'app/Http/Controllers/Admin/UsersController.php',
        'app/Http/Requests/Admin/User/IndexUser.php',
        'app/Http/Requests/Admin/User/StoreUser.php',
        'app/Http/Requests/Admin/User/UpdateUser.php',
        'app/Http/Requests/Admin/User/DestroyUser.php',
        'app/Http/Requests/Admin/User/ImpersonalLoginUser.php',
        'resources/views/admin/user/index.blade.php',
        'resources/views/admin/user/create.blade.php',
        'resources/views/admin/user/edit.blade.php',
        'resources/js/admin/user/Listing.vue',
        'resources/js/admin/user/Form.vue',
        'database/factories/UserFactory.php',
        'routes/admin.php',
        'lang/en/admin.php',
    ];

    /**
     * @param array<string, string|bool|array<string>> $options
     * @param list<string> $expectedFiles
     * @param list<string> $missingFiles
     */
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerate(
        array $options,
        array $expectedFiles,
        array $missingFiles,
        bool $expectsPermissionsMigration = false,
    ): void {
        foreach ($expectedFiles as $relativePath) {
            if (in_array($relativePath, self::PRE_EXISTING_APPEND_TARGETS, true)) {
                continue;
            }
            self::assertFileDoesNotExist($this->app->basePath($relativePath));
        }
        foreach ($missingFiles as $relativePath) {
            self::assertFileDoesNotExist($this->app->basePath($relativePath));
        }

        if ($expectsPermissionsMigration) {
            $this->artisan('admin:generate:user', $options)
                ->expectsConfirmation('Do you want to attach generated permissions to the default role now?', 'no');
        } else {
            $this->artisan('admin:generate:user', $options);
        }

        foreach ($expectedFiles as $relativePath) {
            $path = $this->app->basePath($relativePath);
            self::assertFileExists($path);
            self::assertMatchesFileSnapshot($path);
        }
        foreach ($missingFiles as $relativePath) {
            self::assertFileDoesNotExist($this->app->basePath($relativePath));
        }

        if ($expectsPermissionsMigration) {
            $migrationFiles = glob(
                $this->app->basePath('database/migrations/*_fill_permissions_for_user.php'),
            );
            self::assertIsArray($migrationFiles);
            self::assertCount(1, $migrationFiles);
            self::assertMatchesFileSnapshot($migrationFiles[0]);
        }
    }

    public function testGeneratorWithForceShouldOverwriteFiles(): void
    {
        $filePaths = [
            'app/Http/Controllers/Admin/UsersController.php',
            'app/Http/Requests/Admin/User/IndexUser.php',
            'app/Http/Requests/Admin/User/StoreUser.php',
            'app/Http/Requests/Admin/User/UpdateUser.php',
            'app/Http/Requests/Admin/User/DestroyUser.php',
            'app/Http/Requests/Admin/User/BulkDestroyUser.php',
            'app/Http/Requests/Admin/User/ImpersonalLoginUser.php',
            'resources/views/admin/user/index.blade.php',
            'resources/views/admin/user/create.blade.php',
            'resources/views/admin/user/edit.blade.php',
            'resources/js/admin/user/Listing.vue',
            'resources/js/admin/user/Form.vue',
            'database/factories/UserFactory.php',
        ];

        $this->artisan('admin:generate:user');

        foreach ($filePaths as $relativePath) {
            self::assertFileExists($this->app->basePath($relativePath));
        }

        $this->artisan('admin:generate:user', ['--force' => true]);

        foreach ($filePaths as $relativePath) {
            self::assertFileExists($this->app->basePath($relativePath));
        }
    }

    public static function getCases(): iterable
    {
        $common = self::COMMON_EXPECTED_FILES;
        $defaultController = 'app/Http/Controllers/Admin/UsersController.php';
        $bulkDestroy = 'app/Http/Requests/Admin/User/BulkDestroyUser.php';
        $exportRequest = 'app/Http/Requests/Admin/User/ExportUser.php';
        $exportClass = 'app/Exports/UsersExport.php';
        $defaultModel = 'app/Models/User.php';
        $namespacedModel = 'app/Models/Auth/User.php';
        $outsideModel = 'app/Auth/User.php';

        yield 'default' => [
            'options' => [],
            'expectedFiles' => [...$common, $bulkDestroy],
            'missingFiles' => [$exportRequest, $exportClass, $defaultModel],
        ];

        yield 'with export' => [
            'options' => ['--with-export' => true],
            'expectedFiles' => [...$common, $bulkDestroy, $exportRequest, $exportClass],
            'missingFiles' => [$defaultModel],
        ];

        yield 'without bulk' => [
            'options' => ['--without-bulk' => true],
            'expectedFiles' => $common,
            'missingFiles' => [$bulkDestroy, $exportRequest, $exportClass, $defaultModel],
        ];

        yield 'with export without bulk' => [
            'options' => ['--with-export' => true, '--without-bulk' => true],
            'expectedFiles' => [...$common, $exportRequest, $exportClass],
            'missingFiles' => [$bulkDestroy, $defaultModel],
        ];

        yield 'with media gallery' => [
            'options' => ['--media' => ['gallery:image:public:5000']],
            'expectedFiles' => [...$common, $bulkDestroy],
            'missingFiles' => [$exportRequest, $exportClass, $defaultModel],
        ];

        $commonWithNamespacedController = self::replaceController(
            $common,
            'app/Http/Controllers/Admin/Auth/UsersController.php',
        );

        yield 'with controller-name Auth\\UsersController' => [
            'options' => ['--controller-name' => 'Auth\\UsersController'],
            'expectedFiles' => [...$commonWithNamespacedController, $bulkDestroy],
            'missingFiles' => [$defaultController, $exportRequest, $exportClass, $defaultModel],
        ];

        yield 'with model-name App\\User and controller-name Auth\\UsersController' => [
            'options' => [
                '--model-name' => 'App\\User',
                '--controller-name' => 'Auth\\UsersController',
            ],
            'expectedFiles' => [...$commonWithNamespacedController, $bulkDestroy],
            'missingFiles' => [$defaultController, $exportRequest, $exportClass, $defaultModel],
        ];

        $commonWithOutsideController = self::replaceController(
            $common,
            'app/Http/Controllers/Auth/UsersController.php',
        );

        yield 'with controller-name App\\Http\\Controllers\\Auth\\UsersController' => [
            'options' => ['--controller-name' => 'App\\Http\\Controllers\\Auth\\UsersController'],
            'expectedFiles' => [...$commonWithOutsideController, $bulkDestroy],
            'missingFiles' => [$defaultController, $exportRequest, $exportClass, $defaultModel],
        ];

        $commonForAuthUser = self::commonForSubNamespace('Auth\\User');
        $bulkDestroyAuth = 'app/Http/Requests/Admin/Auth/User/BulkDestroyUser.php';
        $exportRequestAuth = 'app/Http/Requests/Admin/Auth/User/ExportUser.php';

        yield 'with model-name Auth\\User' => [
            'options' => ['--model-name' => 'Auth\\User'],
            'expectedFiles' => [...$commonForAuthUser, $bulkDestroyAuth],
            'missingFiles' => [
                ...self::defaultSubNamespacePaths(),
                $exportRequestAuth,
                $exportClass,
                $defaultModel,
                $namespacedModel,
            ],
        ];

        yield 'with generate-model' => [
            'options' => ['--generate-model' => true],
            'expectedFiles' => [...$common, $bulkDestroy, $defaultModel],
            'missingFiles' => [$exportRequest, $exportClass],
        ];

        yield 'with generate-model and model-name Auth\\User' => [
            'options' => ['--generate-model' => true, '--model-name' => 'Auth\\User'],
            'expectedFiles' => [...$commonForAuthUser, $bulkDestroyAuth, $namespacedModel],
            'missingFiles' => [
                ...self::defaultSubNamespacePaths(),
                $exportRequestAuth,
                $exportClass,
                $defaultModel,
            ],
        ];

        yield 'with generate-model and model-name App\\Auth\\User' => [
            'options' => ['--generate-model' => true, '--model-name' => 'App\\Auth\\User'],
            'expectedFiles' => [...$common, $bulkDestroy, $outsideModel],
            'missingFiles' => [$exportRequest, $exportClass, $defaultModel, $namespacedModel],
        ];

        yield 'with force-permissions' => [
            'options' => ['--force-permissions' => true],
            'expectedFiles' => [...$common, $bulkDestroy],
            'missingFiles' => [$exportRequest, $exportClass, $defaultModel],
            'expectsPermissionsMigration' => true,
        ];
    }

    /**
     * @param array<int, string> $files
     * @return array<int, string>
     */
    private static function replaceController(array $files, string $newControllerPath): array
    {
        return array_values(array_map(
            static fn (string $path): string => $path === 'app/Http/Controllers/Admin/UsersController.php'
                ? $newControllerPath
                : $path,
            $files,
        ));
    }

    /**
     * Builds the COMMON file list remapped to a sub-namespace (e.g. "Auth\\User"):
     * requests move to app/Http/Requests/Admin/Auth/User/, blade views to
     * resources/views/admin/auth/user/, Vue files to resources/js/admin/auth-user/.
     * Controller/factory/routes/lang paths are unaffected.
     *
     * @return array<int, string>
     */
    private static function commonForSubNamespace(string $subNamespace): array
    {
        $nestedSegments = array_map(
            static fn (string $part): string => lcfirst($part),
            explode('\\', $subNamespace),
        );
        $requestsDir = implode('/', $nestedSegments);
        $bladeDir = strtolower(implode('/', $nestedSegments));
        $jsDir = strtolower(implode('-', $nestedSegments));
        $requestsBaseName = end($nestedSegments);
        $requestsBaseNameUcFirst = ucfirst($requestsBaseName);

        return [
            'app/Http/Controllers/Admin/UsersController.php',
            sprintf('app/Http/Requests/Admin/%s/Index%s.php', self::ucPath($requestsDir), $requestsBaseNameUcFirst),
            sprintf('app/Http/Requests/Admin/%s/Store%s.php', self::ucPath($requestsDir), $requestsBaseNameUcFirst),
            sprintf('app/Http/Requests/Admin/%s/Update%s.php', self::ucPath($requestsDir), $requestsBaseNameUcFirst),
            sprintf('app/Http/Requests/Admin/%s/Destroy%s.php', self::ucPath($requestsDir), $requestsBaseNameUcFirst),
            sprintf(
                'app/Http/Requests/Admin/%s/ImpersonalLogin%s.php',
                self::ucPath($requestsDir),
                $requestsBaseNameUcFirst,
            ),
            sprintf('resources/views/admin/%s/index.blade.php', $bladeDir),
            sprintf('resources/views/admin/%s/create.blade.php', $bladeDir),
            sprintf('resources/views/admin/%s/edit.blade.php', $bladeDir),
            sprintf('resources/js/admin/%s/Listing.vue', $jsDir),
            sprintf('resources/js/admin/%s/Form.vue', $jsDir),
            'database/factories/UserFactory.php',
            'routes/admin.php',
            'lang/en/admin.php',
        ];
    }

    /**
     * Default-namespace paths (flat User/) that must NOT exist when a sub-namespaced
     * model-name is used.
     *
     * @return array<int, string>
     */
    private static function defaultSubNamespacePaths(): array
    {
        return [
            'app/Http/Requests/Admin/User/IndexUser.php',
            'app/Http/Requests/Admin/User/StoreUser.php',
            'app/Http/Requests/Admin/User/UpdateUser.php',
            'app/Http/Requests/Admin/User/DestroyUser.php',
            'app/Http/Requests/Admin/User/BulkDestroyUser.php',
            'app/Http/Requests/Admin/User/ImpersonalLoginUser.php',
            'resources/views/admin/user/index.blade.php',
            'resources/views/admin/user/create.blade.php',
            'resources/views/admin/user/edit.blade.php',
            'resources/js/admin/user/Listing.vue',
            'resources/js/admin/user/Form.vue',
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
