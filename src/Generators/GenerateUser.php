<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators;

use Brackets\AdminGenerator\Generators\Traits\FileManipulations;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Override;
use Symfony\Component\Console\Input\InputOption;

final class GenerateUser extends Command
{
    use FileManipulations;

    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:user';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Scaffold complete admin CRUD for specified user model.
        This differs from admin:generate command in many additional features (password handling, roles, ...).';

    public function __construct(protected readonly Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $tableName = 'users';
        $modelName = $this->option('model-name');
        $controllerName = $this->option('controller-name');
        $force = $this->option('force');
        $withExport = $this->option('with-export');
        $withoutBulk = $this->option('without-bulk');
        $media = $this->option('media');
        $generateModel = $this->option('generate-model');

        if ($force) {
            //remove all files
            if ($generateModel) {
                $this->files->delete($this->laravel->path('Models/User.php'));
            }
            if ($withExport) {
                $this->files->delete($this->laravel->path('Exports/UsersExport.php'));
            }
            $this->files->delete($this->laravel->path('Http/Controllers/Admin/UsersController.php'));
            $this->files->deleteDirectory($this->laravel->path('Http/Requests/Admin/User'));
            $this->files->deleteDirectory($this->laravel->resourcePath('js/admin/user'));
            $this->files->deleteDirectory($this->laravel->resourcePath('views/admin/user'));

            $this->info('Deleting previous files finished.');
        }

        if ($generateModel) {
            $this->call('admin:generate:model', [
                'table_name' => $tableName,
                'class_name' => $modelName,
                '--force' => $force,
                '--template' => 'user',
                '--belongs-to-many' => 'roles',
                '--media' => $media,
            ]);

            //TODO change config/auth.php to use our user model for auth
        }

        // we need to replace this before controller generation happens
        $this->strReplaceInFile(
            $this->laravel->resourcePath('views/admin/layout/sidebar.blade.php'),
            '{{-- Do not delete me :) I\'m also used for auto-generation menu items --}}',
            '<li class="nav-item"><a class="nav-link" href="{{ url(\'admin/users\') }}"><i class="nav-icon fa fa-user"></i> {{ __(\'Manage users\') }}</a></li>
        {{-- Do not delete me :) I\'m also used for auto-generation menu items --}}',
            '|url\(\'admin\/users\'\)|',
        );

        $this->call('admin:generate:controller', [
            'table_name' => $tableName,
            'class_name' => $controllerName,
            '--model-name' => $modelName,
            '--template' => 'user',
            '--belongs-to-many' => 'roles',
            '--with-export' => $withExport,
            '--without-bulk' => $withoutBulk,
            '--media' => $media,
        ]);

        $this->call('admin:generate:request:index', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
        ]);

        $this->call('admin:generate:request:store', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--template' => 'user',
            '--belongs-to-many' => 'roles',
        ]);

        $this->call('admin:generate:request:update', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--template' => 'user',
            '--belongs-to-many' => 'roles',
        ]);

        $this->call('admin:generate:request:destroy', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
        ]);

        if (!$withoutBulk) {
            $this->call('admin:generate:request:bulk-destroy', [
                'table_name' => $tableName,
                '--model-name' => $modelName,
                '--force' => $force,
            ]);
        }

        if ($withExport) {
            $this->call('admin:generate:request:export', [
                'table_name' => $tableName,
                '--model-name' => $modelName,
                '--force' => $force,
            ]);
        }

        $this->call('admin:generate:request:impersonal-login', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
        ]);

        $this->call('admin:generate:routes', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--controller-name' => $controllerName,
            '--template' => 'user',
            '--with-export' => $withExport,
            '--without-bulk' => $withoutBulk,
        ]);

        $this->call('admin:generate:blade-index', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--template' => 'user',
            '--with-export' => $withExport,
            '--without-bulk' => $withoutBulk,
        ]);

        $this->call('admin:generate:vue-listing', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--template' => 'user',
            '--with-export' => $withExport,
            '--without-bulk' => $withoutBulk,
        ]);

        $this->call('admin:generate:blade-create', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--template' => 'user',
            '--belongs-to-many' => 'roles',
            '--media' => $media,
        ]);

        $this->call('admin:generate:blade-edit', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--template' => 'user',
            '--belongs-to-many' => 'roles',
            '--media' => $media,
        ]);

        $this->call('admin:generate:vue-form', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--template' => 'user',
            '--belongs-to-many' => 'roles',
            '--media' => $media,
        ]);

        $this->call('admin:generate:lang', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--belongs-to-many' => 'roles',
            '--with-export' => $withExport,
            '--media' => $media,
        ]);

        $this->call('admin:generate:factory', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
        ]);

        if ($withExport) {
            $this->call('admin:generate:export', [
                'table_name' => $tableName,
                '--force' => $force,
            ]);
        }

        if ($this->shouldGeneratePermissionsMigration() || $this->option('force-permissions')) {
            $this->call('admin:generate:permissions', [
                'table_name' => $tableName,
                '--model-name' => $modelName,
                '--force' => $force,
                '--without-bulk' => $withoutBulk,
                '--with-impersonal-login' => true,
            ]);

            if (
                $this->option('no-interaction')
                || $this->confirm('Do you want to attach generated permissions to the default role now?', true)
            ) {
                $this->call('migrate');
            }
        }

        if ($this->option('seed')) {
            $this->info('Seeding testing data');
            $modelName::factory()->count(20)->create();
        }

        $this->info('Generating whole user admin finished');
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getArguments(): array
    {
        return [
        ];
    }

    /** @return array<array<string|int|null>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Specify custom model name'],
            ['controller-name', 'c', InputOption::VALUE_OPTIONAL, 'Specify custom controller name'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating admin user'],
            ['with-export', 'e', InputOption::VALUE_NONE, 'Generate an option to Export as Excel'],
            ['without-bulk', 'wb', InputOption::VALUE_NONE, 'Generate without bulk options'],
            ['media', 'M', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Media collections (format: name:type:disk:maxFiles)'],
            ['seed', 's', InputOption::VALUE_NONE, 'Seeds table with fake data'],
            ['generate-model', 'g', InputOption::VALUE_NONE, 'Generates model'],
            ['force-permissions', null, InputOption::VALUE_NONE, 'Force generating permissions migration even if the Craftable service provider is not installed'],
        ];
    }

    private function shouldGeneratePermissionsMigration(): bool
    {
        return class_exists('\Brackets\Craftable\CraftableServiceProvider');
    }
}
