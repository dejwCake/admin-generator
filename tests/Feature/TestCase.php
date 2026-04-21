<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature;

use Brackets\AdminGenerator\AdminGeneratorServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
        $schemaBuilder = $app['db']->connection()->getSchemaBuilder();

        $schemaBuilder->create('users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        $schemaBuilder->create('admin_users', static function (Blueprint $table): void {
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

        if (env('DB_CONNECTION') === 'pgsql') {
            $schemaBuilder->table(
                'admin_users',
                //phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                static function (Blueprint $table): void {
                    DB::statement(
                        'CREATE UNIQUE INDEX admin_users_email_null_deleted_at ON admin_users (email) '
                        . 'WHERE deleted_at IS NULL;',
                    );
                },
            );
        }

        $schemaBuilder->create('password_resets', static function (Blueprint $table): void {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        $tableNames = [
            'roles' => 'roles',
            'permissions' => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles' => 'model_has_roles',
            'role_has_permissions' => 'role_has_permissions',
        ];

        $schemaBuilder->create($tableNames['permissions'], static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        $schemaBuilder->create($tableNames['roles'], static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        $schemaBuilder->create(
            $tableNames['model_has_permissions'],
            static function (Blueprint $table) use ($tableNames): void {
                $table->integer('permission_id')->unsigned();
                $table->morphs('model');

                $table->foreign('permission_id')
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');

                $table->primary(['permission_id', 'model_id', 'model_type']);
            },
        );

        $schemaBuilder->create(
            $tableNames['model_has_roles'],
            static function (Blueprint $table) use ($tableNames): void {
                $table->integer('role_id')->unsigned();
                $table->morphs('model');

                $table->foreign('role_id')
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');

                $table->primary(['role_id', 'model_id', 'model_type']);
            },
        );

        $schemaBuilder->create(
            $tableNames['role_has_permissions'],
            static function (Blueprint $table) use ($tableNames): void {
                $table->integer('permission_id')->unsigned();
                $table->integer('role_id')->unsigned();

                $table->foreign('permission_id')
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');

                $table->foreign('role_id')
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');

                $table->primary(['permission_id', 'role_id']);
            },
        );

        $schemaBuilder->create('categories', static function (Blueprint $table): void {
            $table->increments('id');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('title')->unique();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('subject')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->string('language', 2)->default('en');
            $table->string('slug')->unique();
            $table->text('perex')->nullable();
            $table->longText('long_text')->nullable();
            $table->date('published_at')->nullable();
            $table->date('date_start')->nullable();
            $table->time('time_start')->nullable();
            $table->dateTime('date_time_end')->nullable();
            $table->dateTime('released_at');
            $table->jsonb('text');
            $table->json('description');
            $table->boolean('enabled')->default(false);
            $table->boolean('send')->default(true);
            $table->decimal('price', 10, 2)->nullable();
            $table->float('rating')->nullable();
            $table->integer('views')->default(0);
            $table->unsignedInteger('created_by_admin_user_id')->nullable();
            $table->foreign('created_by_admin_user_id')->references('id')->on('admin_users');
            $table->unsignedInteger('updated_by_admin_user_id')->nullable();
            $table->foreign('updated_by_admin_user_id')->references('id')->on('admin_users');
            $table->timestamps();
            $table->softDeletes();
        });

        $schemaBuilder->create('posts', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('title');
        });

        $schemaBuilder->create('category_post', static function (Blueprint $table): void {
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

        File::copyDirectory(__DIR__ . '/../fixtures/resources', resource_path());

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
        } elseif (env('DB_CONNECTION') === 'mysql') {
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
