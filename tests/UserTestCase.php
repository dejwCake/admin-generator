<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests;

use Brackets\AdminGenerator\AdminGeneratorServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Snapshots\MatchesSnapshots;

abstract class UserTestCase extends Orchestra
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
        $app['db']->connection()->getSchemaBuilder()->create('users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

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

        if (env('DB_CONNECTION') === 'pgsql') {
            $app['db']->connection()->getSchemaBuilder()->table(
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

        $app['db']->connection()->getSchemaBuilder()->create(
            'password_resets',
            static function (Blueprint $table): void {
                $table->string('email')->index();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            },
        );

        $tableNames = [
            'roles' => 'roles',
            'permissions' => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles' => 'model_has_roles',
            'role_has_permissions' => 'role_has_permissions',
        ];

        $app['db']->connection()->getSchemaBuilder()->create(
            $tableNames['permissions'],
            static function (Blueprint $table): void {
                $table->increments('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            },
        );

        $app['db']->connection()->getSchemaBuilder()->create(
            $tableNames['roles'],
            static function (Blueprint $table): void {
                $table->increments('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            },
        );

        $app['db']->connection()->getSchemaBuilder()->create(
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

        $app['db']->connection()->getSchemaBuilder()->create(
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

        $app['db']->connection()->getSchemaBuilder()->create(
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
}
