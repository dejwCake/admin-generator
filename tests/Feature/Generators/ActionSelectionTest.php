<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators;

use Brackets\AdminGenerator\Tests\Feature\TestCase;
use InvalidArgumentException;

use function escapeshellarg;
use function exec;
use function file_get_contents;
use function glob;
use function implode;
use function sprintf;
use function strpos;

final class ActionSelectionTest extends TestCase
{
    public function testOnlyShowAndExportProducesAReadOnlyResource(): void
    {
        $this->artisan('admin:generate', [
            'table_name' => 'categories',
            '--only' => 'show,export',
            '--force-permissions' => true,
        ])->expectsConfirmation('Do you want to attach generated permissions to the default role now?', 'no');

        $routes = (string) file_get_contents($this->app->basePath('routes/admin/categories.php'));
        self::assertStringContainsString("'show'", $routes);
        self::assertStringContainsString("'export'", $routes);
        self::assertStringNotContainsString("'create'", $routes);
        self::assertStringNotContainsString("'edit'", $routes);
        self::assertStringNotContainsString("'destroy'", $routes);
        self::assertStringNotContainsString("'bulk-destroy'", $routes);

        $controller = (string) file_get_contents(
            $this->app->basePath('app/Http/Controllers/Admin/CategoriesController.php'),
        );
        self::assertStringContainsString('public function show(', $controller);
        self::assertStringContainsString('public function export(', $controller);
        self::assertStringNotContainsString('public function create(', $controller);
        self::assertStringNotContainsString('public function edit(', $controller);

        self::assertFileDoesNotExist($this->app->basePath('resources/views/admin/category/create.blade.php'));
        self::assertFileDoesNotExist($this->app->basePath('resources/views/admin/category/edit.blade.php'));
        self::assertFileExists($this->app->basePath('resources/views/admin/category/show.blade.php'));
    }

    public function testExceptRemovesOnlyWhatItNames(): void
    {
        $this->artisan('admin:generate', [
            'table_name' => 'categories',
            '--except' => 'delete,bulk-delete',
            '--force-permissions' => true,
        ])->expectsConfirmation('Do you want to attach generated permissions to the default role now?', 'no');

        $routes = (string) file_get_contents($this->app->basePath('routes/admin/categories.php'));

        self::assertStringContainsString("'create'", $routes);
        self::assertStringContainsString("'edit'", $routes);
        self::assertStringContainsString("'show'", $routes);
        self::assertStringNotContainsString("'destroy'", $routes);
        self::assertStringNotContainsString("'bulk-destroy'", $routes);
        self::assertStringContainsString(
            "'export'",
            $routes,
            '--except subtracts from every action, so export is on unless excluded.',
        );
    }

    /**
     * The route file must survive any selection: `/{id}` would swallow `/export` if show were not
     * emitted last.
     */
    public function testShowNeverShadowsExport(): void
    {
        $this->artisan('admin:generate', [
            'table_name' => 'categories',
            '--only' => 'show,export',
            '--force-permissions' => true,
        ])->expectsConfirmation('Do you want to attach generated permissions to the default role now?', 'no');

        $routes = (string) file_get_contents($this->app->basePath('routes/admin/categories.php'));

        self::assertLessThan(
            strpos($routes, "'show'"),
            strpos($routes, "'export'"),
            'The export route must be declared before the catch-all show route.',
        );
    }

    public function testBothOptionsTogetherIsRejected(): void
    {
        try {
            $this->artisan('admin:generate', [
                'table_name' => 'categories',
                '--only' => 'show',
                '--except' => 'delete',
            ]);

            self::fail('Combining --only and --except should be rejected.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('cannot be combined', $exception->getMessage());
        }
    }

    /**
     * Removing an action from the routes is only half of it: the controller still built
     * `createUrl` and friends from routes that no longer existed, and imported request classes that
     * were no longer generated. A trimmed resource has to be coherent, not merely smaller.
     */
    public function testATrimmedResourceReferencesNothingItDoesNotGenerate(): void
    {
        $this->generate(['--only' => 'show']);

        $controller = $this->read('app/Http/Controllers/Admin/CategoriesController.php');
        $index = $this->read('resources/views/admin/category/index.blade.php');
        $listing = $this->read('resources/js/admin/category/Listing.vue');

        foreach (['createUrl', 'editUrlTemplate', 'updateUrlTemplate', 'destroyUrlTemplate'] as $url) {
            self::assertStringNotContainsString($url, $controller, sprintf('%s in the controller', $url));
            self::assertStringNotContainsString($url, $index, sprintf('%s in the index view', $url));
            self::assertStringNotContainsString($url, $listing, sprintf('%s in the listing', $url));
        }

        foreach (['StoreCategory', 'UpdateCategory', 'DestroyCategory', 'BulkDestroyCategory'] as $request) {
            self::assertStringNotContainsString($request, $controller, sprintf('%s is imported', $request));
            self::assertFileDoesNotExist(
                $this->app->basePath(sprintf('app/Http/Requests/Admin/Category/%s.php', $request)),
            );
        }

        self::assertStringContainsString(
            'ReadOnlySwitch',
            $listing,
            'Without an update route a boolean has nowhere to post, so it renders read-only.',
        );
    }

    /**
     * A generated file that does not parse is the failure a snapshot cannot see.
     */
    public function testEveryGeneratedPhpFileParses(): void
    {
        $this->generate(['--only' => 'show,export']);

        foreach (
            [
            'app/Http/Controllers/Admin/CategoriesController.php',
            'app/Http/Requests/Admin/Category/IndexCategory.php',
            'app/Exports/CategoriesExport.php',
            'routes/admin/categories.php',
            'lang/en/admin.php',
            ] as $file
        ) {
            $path = $this->app->basePath($file);

            self::assertFileExists($path);

            $output = [];
            $status = 0;
            exec(sprintf('php -l %s 2>&1', escapeshellarg($path)), $output, $status);

            self::assertSame(0, $status, sprintf('%s: %s', $file, implode(' ', $output)));
        }
    }

    public function testPermissionsAlwaysCarryTheFullSet(): void
    {
        $this->artisan('admin:generate', [
            'table_name' => 'categories',
            '--only' => 'show',
            '--force-permissions' => true,
        ])->expectsConfirmation('Do you want to attach generated permissions to the default role now?', 'no');

        $migrations = glob($this->app->basePath('database/migrations/*_fill_permissions_for_category.php'));
        self::assertNotEmpty($migrations);

        $contents = (string) file_get_contents($migrations[0]);
        foreach (['index', 'create', 'show', 'edit', 'delete', 'bulk-delete'] as $permission) {
            self::assertStringContainsString(
                sprintf("'admin.category.%s'", $permission),
                $contents,
                sprintf('Permissions must stay complete regardless of the selection (%s).', $permission),
            );
        }
    }

    /**
     * @param array<string, string|bool> $options
     */
    private function generate(array $options): void
    {
        $this->artisan('admin:generate', [
            'table_name' => 'categories',
            '--force-permissions' => true,
            ...$options,
        ])->expectsConfirmation('Do you want to attach generated permissions to the default role now?', 'no');
    }

    private function read(string $file): string
    {
        return (string) file_get_contents($this->app->basePath($file));
    }
}
