<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator;

use Brackets\AdminGenerator\Generate\Traits\FileManipulations;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class GenerateUser extends Command
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
        $tableNameArgument = 'users';
        $modelOption = $this->option('model-name');
        $controllerOption = $this->option('controller-name');
        $generateModelOption = $this->option('generate-model');
        $exportOption = $this->option('with-export');
        $force = $this->option('force');

        if ($force) {
            //remove all files
            if ($generateModelOption) {
                $this->files->delete(app_path('Models/User.php'));
            }
            if ($exportOption) {
                $this->files->delete(app_path('Exports/UsersExport.php'));
            }
            $this->files->delete(app_path('Http/Controllers/Admin/UsersController.php'));
            $this->files->deleteDirectory(app_path('Http/Requests/Admin/User'));
            $this->files->deleteDirectory(resource_path('js/admin/user'));
            $this->files->deleteDirectory(resource_path('views/admin/user'));

            $this->info('Deleting previous files finished.');
        }

        if ($generateModelOption) {
            $this->call('admin:generate:model', [
                'table_name' => $tableNameArgument,
                'class_name' => $modelOption,
                '--template' => 'user',
                '--belongs-to-many' => 'roles',
            ]);

            //TODO change config/auth.php to use our user model for auth
        }

        // we need to replace this before controller generation happens
        $this->strReplaceInFile(
            resource_path('views/admin/layout/sidebar.blade.php'),
            '{{-- Do not delete me :) I\'m also used for auto-generation menu items --}}',
            '<li class="nav-item"><a class="nav-link" href="{{ url(\'admin/users\') }}"><i class="nav-icon icon-user"></i> {{ __(\'Manage users\') }}</a></li>
            {{-- Do not delete me :) I\'m also used for auto-generation menu items --}}',
            '|url\(\'admin\/users\'\)|',
        );

        $this->call('admin:generate:controller', [
            'table_name' => $tableNameArgument,
            'class_name' => $controllerOption,
            '--model-name' => $modelOption,
            '--template' => 'user',
            '--belongs-to-many' => 'roles',
            '--with-export' => $exportOption,
        ]);

        $this->call('admin:generate:request:index', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
        ]);

        $this->call('admin:generate:request:store', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--template' => 'user',
            '--belongs-to-many' => 'roles',
        ]);

        $this->call('admin:generate:request:update', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--template' => 'user',
            '--belongs-to-many' => 'roles',
        ]);

        $this->call('admin:generate:request:destroy', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
        ]);

        $this->call('admin:generate:routes', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--controller-name' => $controllerOption,
            '--template' => 'user',
            '--with-export' => $exportOption,
        ]);

        $this->call('admin:generate:index', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--template' => 'user',
            '--with-export' => $exportOption,
        ]);

        $this->call('admin:generate:form', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--belongs-to-many' => 'roles',
            '--template' => 'user',
        ]);

        $this->call('admin:generate:lang', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--template' => 'user',
            '--belongs-to-many' => 'roles',
            '--with-export' => $exportOption,
        ]);

        $this->call('admin:generate:factory', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--template' => 'user',
            '--force' => $force,
        ]);

        if ($exportOption) {
            $this->call('admin:generate:export', [
                'table_name' => $tableNameArgument,
                '--force' => $force,
            ]);
        }

        if ($this->shouldGeneratePermissionsMigration()) {
            $this->call('admin:generate:permissions', [
                'table_name' => $tableNameArgument,
                '--model-name' => $modelOption,
                '--force' => $force,
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
            $modelOption::factory()->count(20)->create();
        }

        $this->info('Generating whole user admin finished');
    }

    /** @return array<array<string|int>> */
    protected function getArguments(): array
    {
        return [
        ];
    }

    /** @return array<array<string|int>> */
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Specify custom model name'],
            ['controller-name', 'c', InputOption::VALUE_OPTIONAL, 'Specify custom controller name'],
            ['generate-model', 'g', InputOption::VALUE_NONE, 'Generates model'],

            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating admin user'],
            ['seed', 's', InputOption::VALUE_NONE, 'Seeds table with fake data'],
            ['with-export', 'e', InputOption::VALUE_NONE, 'Generate an option to Export as Excel'],
        ];
    }

    protected function shouldGeneratePermissionsMigration(): bool
    {
        return class_exists('\Brackets\Craftable\CraftableServiceProvider');
    }
}
