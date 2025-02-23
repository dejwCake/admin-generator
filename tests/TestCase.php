<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests;

use Brackets\AdminGenerator\AdminGeneratorServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Snapshots\MatchesSnapshots;
use SplFileInfo;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;
    use MatchesSnapshots;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function setUpDatabase(Application $app): void
    {
        $app['db']->connection()->getSchemaBuilder()->create('admin_users', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->string('password');
            $table->rememberToken();

            $table->boolean('activated')->default(false);
            $table->boolean('forbidden')->default(false);
            $table->string('language', 2)->default('en');

            $table->softDeletes('deleted_at');
            $table->timestamps();

            $table->unique(['email', 'deleted_at']);
        });

        $app['db']->connection()->getSchemaBuilder()->create('categories', static function (Blueprint $table): void {
            $table->increments('id');
            $table->foreignId('user_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('perex')->nullable();
            $table->date('published_at')->nullable();
            $table->date('date_start')->nullable();
            $table->time('time_start')->nullable();
            $table->dateTime('date_time_end')->nullable();
            $table->jsonb('text');
            $table->json('description');
            $table->boolean('enabled')->default(false);
            $table->boolean('send')->default(true);
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('views')->default(0);
            $table->unsignedInteger('created_by_admin_user_id')->nullable();
            $table->foreign('created_by_admin_user_id')->references('id')->on('admin_users');
            $table->unsignedInteger('updated_by_admin_user_id')->nullable();
            $table->foreign('updated_by_admin_user_id')->references('id')->on('admin_users');
            $table->timestamps();
            $table->softDeletes();
        });

        $app['db']->connection()->getSchemaBuilder()->create('posts', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('title');
        });

        $app['db']->connection()->getSchemaBuilder()->create('category_post', static function (Blueprint $table): void {
            $table->unsignedInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->unsignedInteger('post_id')->nullable();
            $table->foreign('post_id')->references('id')->on('posts');
        });
    }

    /**
     * @param Application $app
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    protected function getEnvironmentSetUp($app): void
    {
        $newBasePath = $app->basePath() . DIRECTORY_SEPARATOR . 'testing_folder';

        $app->getNamespace();
        $app->setBasePath($newBasePath);
        $this->initializeDirectory($newBasePath);

        File::copyDirectory(__DIR__ . '/fixtures/resources', resource_path());

        if (env('DB_CONNECTION') === 'pgsql') {
            $app['config']->set('database.default', 'pgsql');
            $app['config']->set('database.connections.pgsql', [
                'driver' => 'pgsql',
                'host' => 'pgsql',
                'port' => '5432',
                'database' => env('DB_DATABASE', 'laravel'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', 'bestsecret'),
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
                'sslmode' => 'prefer',
            ]);
        } else if (env('DB_CONNECTION') === 'mysql') {
            $app['config']->set('database.default', 'mysql');
            $app['config']->set('database.connections.mysql', [
                'driver' => 'mysql',
                'host' => 'mysql',
                'port' => '3306',
                'database' => env('DB_DATABASE', 'laravel'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', 'bestsecret'),
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
                'sslmode' => 'prefer',
            ]);
        } else {
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections.sqlite', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);
        }
    }

    /**
     * @param Application $app
     * @return array<class-string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    protected function getPackageProviders($app): array
    {
        return [
            AdminGeneratorServiceProvider::class,
        ];
    }

    protected function initializeDirectory(string $directory): void
    {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }
        File::makeDirectory($directory);
    }

    protected function getPermissionMigrationPath(string $fileName): ?string
    {
        $file = (new Collection(File::files(database_path('migrations'))))
            ->filter(static fn (SplFileInfo $file) => str_contains($file->getFilename(), $fileName))
            ->first();
        if ($file === null) {
            return null;
        }
        assert($file instanceof SplFileInfo);

        return $file->getPathname();
    }
}
