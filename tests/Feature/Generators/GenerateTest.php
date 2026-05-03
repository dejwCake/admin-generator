<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;

final class GenerateTest extends TestCase
{
    private const array PRE_EXISTING_APPEND_TARGETS = [
        'routes/admin.php',
        'lang/en/admin.php',
    ];

    /**
     * @param array<string, string|bool|array<string>> $arguments
     * @param list<string> $expectedFiles
     * @param list<string> $missingFiles
     */
    #[DataProvider('getCases')]
    public function testGeneratorShouldGenerate(
        array $arguments,
        array $expectedFiles,
        array $missingFiles,
        bool $expectsPermissionsMigration = false,
        ?string $permissionsMigrationSlug = null,
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
            $this->artisan('admin:generate', $arguments)
                ->expectsConfirmation('Do you want to attach generated permissions to the default role now?', 'no');
        } else {
            $this->artisan('admin:generate', $arguments);
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
                $this->app->basePath(
                    sprintf('database/migrations/*_fill_permissions_for_%s.php', $permissionsMigrationSlug),
                ),
            );
            self::assertIsArray($migrationFiles);
            self::assertCount(1, $migrationFiles);
            self::assertMatchesFileSnapshot($migrationFiles[0]);
        }
    }

    public function testGeneratorWithForceShouldOverwriteFiles(): void
    {
        $filePaths = [
            'app/Models/Category.php',
            'app/Http/Controllers/Admin/CategoriesController.php',
            'app/Http/Requests/Admin/Category/IndexCategory.php',
            'app/Http/Requests/Admin/Category/StoreCategory.php',
            'app/Http/Requests/Admin/Category/UpdateCategory.php',
            'app/Http/Requests/Admin/Category/DestroyCategory.php',
            'app/Http/Requests/Admin/Category/BulkDestroyCategory.php',
            'resources/views/admin/category/index.blade.php',
            'resources/views/admin/category/create.blade.php',
            'resources/views/admin/category/edit.blade.php',
            'resources/js/admin/category/Listing.vue',
            'resources/js/admin/category/Form.vue',
            'database/factories/CategoryFactory.php',
        ];

        $this->artisan('admin:generate', ['table_name' => 'categories']);

        foreach ($filePaths as $relativePath) {
            self::assertFileExists($this->app->basePath($relativePath));
        }

        $this->artisan('admin:generate', ['table_name' => 'categories', '--force' => true]);

        foreach ($filePaths as $relativePath) {
            self::assertFileExists($this->app->basePath($relativePath));
        }
    }

    public static function getCases(): iterable
    {
        $categoriesCommon = self::commonForTable('Category');
        $defaultCategoriesController = 'app/Http/Controllers/Admin/CategoriesController.php';
        $categoriesBulk = 'app/Http/Requests/Admin/Category/BulkDestroyCategory.php';
        $categoriesExportRequest = 'app/Http/Requests/Admin/Category/ExportCategory.php';
        $categoriesExportClass = 'app/Exports/CategoriesExport.php';

        yield 'categories default' => [
            'arguments' => ['table_name' => 'categories'],
            'expectedFiles' => [...$categoriesCommon, $categoriesBulk],
            'missingFiles' => [$categoriesExportRequest, $categoriesExportClass],
        ];

        yield 'categories with export' => [
            'arguments' => ['table_name' => 'categories', '--with-export' => true],
            'expectedFiles' => [
                ...$categoriesCommon,
                $categoriesBulk,
                $categoriesExportRequest,
                $categoriesExportClass,
            ],
            'missingFiles' => [],
        ];

        yield 'categories without bulk' => [
            'arguments' => ['table_name' => 'categories', '--without-bulk' => true],
            'expectedFiles' => $categoriesCommon,
            'missingFiles' => [$categoriesBulk, $categoriesExportRequest, $categoriesExportClass],
        ];

        yield 'categories with export without bulk' => [
            'arguments' => [
                'table_name' => 'categories',
                '--with-export' => true,
                '--without-bulk' => true,
            ],
            'expectedFiles' => [...$categoriesCommon, $categoriesExportRequest, $categoriesExportClass],
            'missingFiles' => [$categoriesBulk],
        ];

        yield 'categories with media gallery' => [
            'arguments' => [
                'table_name' => 'categories',
                '--media' => ['gallery:image:public:5000'],
            ],
            'expectedFiles' => [...$categoriesCommon, $categoriesBulk],
            'missingFiles' => [$categoriesExportRequest, $categoriesExportClass],
        ];

        yield 'categories with belongs-to-many posts' => [
            'arguments' => ['table_name' => 'categories', '--belongs-to-many' => 'posts'],
            'expectedFiles' => [...$categoriesCommon, $categoriesBulk],
            'missingFiles' => [$categoriesExportRequest, $categoriesExportClass],
        ];

        $commonForBillingCat = self::commonForSubNamespace('Billing\\Cat', 'Categories');

        yield 'categories with model-name Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'Billing\\Cat'],
            'expectedFiles' => [
                ...$commonForBillingCat,
                'app/Http/Requests/Admin/Billing/Cat/BulkDestroyCat.php',
            ],
            'missingFiles' => [
                ...self::defaultCategoriesPaths(),
                'app/Http/Requests/Admin/Billing/Cat/ExportCat.php',
            ],
        ];

        $commonForOutsideCat = self::commonForOutsideModelNamespace('Cat', 'Categories', 'Billing');

        yield 'categories with model-name App\\Billing\\Cat' => [
            'arguments' => ['table_name' => 'categories', '--model-name' => 'App\\Billing\\Cat'],
            'expectedFiles' => [
                ...$commonForOutsideCat,
                'app/Http/Requests/Admin/Cat/BulkDestroyCat.php',
            ],
            'missingFiles' => [
                ...self::defaultCategoriesPaths(),
                'app/Models/Cat.php',
                'app/Models/Billing/Cat.php',
                'app/Http/Requests/Admin/Cat/ExportCat.php',
            ],
        ];

        yield 'categories with controller-name Auth\\CategoriesController' => [
            'arguments' => [
                'table_name' => 'categories',
                '--controller-name' => 'Auth\\CategoriesController',
            ],
            'expectedFiles' => [
                ...self::replaceController(
                    $categoriesCommon,
                    'app/Http/Controllers/Admin/Auth/CategoriesController.php',
                ),
                $categoriesBulk,
            ],
            'missingFiles' => [$defaultCategoriesController, $categoriesExportRequest, $categoriesExportClass],
        ];

        yield 'categories with controller-name App\\Http\\Controllers\\Auth\\CategoriesController' => [
            'arguments' => [
                'table_name' => 'categories',
                '--controller-name' => 'App\\Http\\Controllers\\Auth\\CategoriesController',
            ],
            'expectedFiles' => [
                ...self::replaceController($categoriesCommon, 'app/Http/Controllers/Auth/CategoriesController.php'),
                $categoriesBulk,
            ],
            'missingFiles' => [$defaultCategoriesController, $categoriesExportRequest, $categoriesExportClass],
        ];

        yield 'categories with force-permissions' => [
            'arguments' => ['table_name' => 'categories', '--force-permissions' => true],
            'expectedFiles' => [...$categoriesCommon, $categoriesBulk],
            'missingFiles' => [$categoriesExportRequest, $categoriesExportClass],
            'expectsPermissionsMigration' => true,
            'permissionsMigrationSlug' => 'category',
        ];

        $postsCommon = self::commonForTable('Post');
        $postsBulk = 'app/Http/Requests/Admin/Post/BulkDestroyPost.php';
        $postsExportRequest = 'app/Http/Requests/Admin/Post/ExportPost.php';
        $postsExportClass = 'app/Exports/PostsExport.php';

        yield 'posts default' => [
            'arguments' => ['table_name' => 'posts'],
            'expectedFiles' => [...$postsCommon, $postsBulk],
            'missingFiles' => [$postsExportRequest, $postsExportClass],
        ];

        yield 'posts with belongs-to-many categories' => [
            'arguments' => ['table_name' => 'posts', '--belongs-to-many' => 'categories'],
            'expectedFiles' => [...$postsCommon, $postsBulk],
            'missingFiles' => [$postsExportRequest, $postsExportClass],
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function commonForTable(string $modelBaseName): array
    {
        $kebab = strtolower((string) preg_replace('/(?<!^)([A-Z])/', '-$1', $modelBaseName));
        $plural = Str::plural($modelBaseName);
        $resource = self::resourceKebab($plural);

        return [
            sprintf('app/Models/%s.php', $modelBaseName),
            sprintf('app/Http/Controllers/Admin/%sController.php', $plural),
            sprintf('app/Http/Requests/Admin/%s/Index%s.php', $modelBaseName, $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/Store%s.php', $modelBaseName, $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/Update%s.php', $modelBaseName, $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/Destroy%s.php', $modelBaseName, $modelBaseName),
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
     * Builds the expected-file list for a nested --model-name (e.g. "Billing\\CategOry"):
     * requests move to app/Http/Requests/Admin/Billing/CategOry/, blade views to
     * resources/views/admin/billing/categ-ory/, Vue files to resources/js/admin/billing-categ-ory/,
     * factory to database/factories/CategOryFactory.php, model to app/Models/Billing/CategOry.php.
     *
     * @return array<int, string>
     */
    private static function commonForSubNamespace(string $subNamespace, string $pluralBaseName): array
    {
        $segments = explode('\\', $subNamespace);
        $modelBaseName = (string) end($segments);
        $nestedSegments = array_map(
            static fn (string $part): string => lcfirst($part),
            $segments,
        );
        $requestsDir = implode('/', $nestedSegments);
        $bladeAndJsSegments = array_map(
            static fn (string $part): string => strtolower(
                (string) preg_replace('/(?<!^)([A-Z])/', '-$1', $part),
            ),
            $nestedSegments,
        );
        $bladeDir = implode('/', $bladeAndJsSegments);
        $jsDir = implode('-', $bladeAndJsSegments);
        $modelPath = sprintf('app/Models/%s.php', str_replace('\\', '/', $subNamespace));

        $namespaceParts = explode('\\', $subNamespace);
        array_pop($namespaceParts);
        $namespaceParts[] = Str::plural($modelBaseName);
        $resource = self::resourceKebab(implode('', $namespaceParts));

        return [
            $modelPath,
            sprintf('app/Http/Controllers/Admin/%sController.php', $pluralBaseName),
            sprintf('app/Http/Requests/Admin/%s/Index%s.php', self::ucPath($requestsDir), $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/Store%s.php', self::ucPath($requestsDir), $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/Update%s.php', self::ucPath($requestsDir), $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/Destroy%s.php', self::ucPath($requestsDir), $modelBaseName),
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
     * Builds the expected-file list for a --model-name that already starts with "App\\" but is
     * outside "App\\Models\\" (e.g. "App\\Billing\\CategOry"): requests/views/Vue/factory paths
     * flatten to the model base name; the model file itself lives at the custom path.
     *
     * @return array<int, string>
     */
    private static function commonForOutsideModelNamespace(
        string $modelBaseName,
        string $pluralBaseName,
        string $modelSubPath,
    ): array {
        $kebab = strtolower((string) preg_replace('/(?<!^)([A-Z])/', '-$1', $modelBaseName));
        $resource = self::resourceKebab(Str::plural($modelBaseName));

        return [
            sprintf('app/%s/%s.php', $modelSubPath, $modelBaseName),
            sprintf('app/Http/Controllers/Admin/%sController.php', $pluralBaseName),
            sprintf('app/Http/Requests/Admin/%s/Index%s.php', $modelBaseName, $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/Store%s.php', $modelBaseName, $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/Update%s.php', $modelBaseName, $modelBaseName),
            sprintf('app/Http/Requests/Admin/%s/Destroy%s.php', $modelBaseName, $modelBaseName),
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
     * Default-namespace paths (flat Category/) that must NOT exist when a sub-namespaced
     * model-name is used.
     *
     * @return array<int, string>
     */
    private static function defaultCategoriesPaths(): array
    {
        return [
            'app/Http/Requests/Admin/Category/IndexCategory.php',
            'app/Http/Requests/Admin/Category/StoreCategory.php',
            'app/Http/Requests/Admin/Category/UpdateCategory.php',
            'app/Http/Requests/Admin/Category/DestroyCategory.php',
            'app/Http/Requests/Admin/Category/BulkDestroyCategory.php',
            'resources/views/admin/category/index.blade.php',
            'resources/views/admin/category/create.blade.php',
            'resources/views/admin/category/edit.blade.php',
            'resources/js/admin/category/Listing.vue',
            'resources/js/admin/category/Form.vue',
            'database/factories/CategoryFactory.php',
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

    private static function ucPath(string $path): string
    {
        return implode('/', array_map(
            static fn (string $part): string => ucfirst($part),
            explode('/', $path),
        ));
    }

    private static function resourceKebab(string $studlyConcat): string
    {
        return strtolower((string) preg_replace('/(?<!^)([A-Z])/', '-$1', $studlyConcat));
    }
}
