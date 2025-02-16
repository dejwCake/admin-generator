<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator;

use Brackets\AdminGenerator\Generate\Traits\FileManipulations;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class GenerateAdminUser extends Command
{
    use FileManipulations;

    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:admin-user';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Scaffold complete admin CRUD for specified admin user model from admin-auth package. 
        This differs from admin:generate command in many additional features (password handling, roles, ...).';

    public function __construct(protected readonly Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $tableNameArgument = 'admin_users';
        $modelOption = $this->option('model-name');
        $controllerOption = $this->option('controller-name');
        $exportOption = $this->option('with-export');
        $force = $this->option('force');

        if ($modelOption === null) {
            $modelOption = 'AdminUser';
            $modelWithFullNamespace = 'Brackets\AdminAuth\Models\AdminUser';
        } else {
            $modelWithFullNamespace = null;
        }

        if ($force) {
            if ($exportOption) {
                $this->files->delete(app_path('Exports/AdminUsersExport.php'));
            }
            $this->files->delete(app_path('Http/Controllers/Admin/AdminUsersController.php'));
            $this->files->delete(database_path('Factories/AdminUserFactory.php'));
            $this->files->deleteDirectory(app_path('Http/Requests/Admin/AdminUser'));
            $this->files->deleteDirectory(resource_path('js/admin/admin-user'));
            $this->files->deleteDirectory(resource_path('views/admin/admin-user'));

            $this->info('Deleting previous files finished.');
        }

        // we need to replace this before controller generation happens
        $this->strReplaceInFile(
            resource_path('views/admin/layout/sidebar.blade.php'),
            '{{-- Do not delete me :) I\'m also used for auto-generation menu items --}}',
            '<li class="nav-item"><a class="nav-link" href="{{ url(\'admin/admin-users\') }}"><i class="nav-icon icon-user"></i> {{ __(\'Manage access\') }}</a></li>            
            {{-- Do not delete me :) I\'m also used for auto-generation menu items --}}',
            '|url\(\'admin\/admin-users\'\)|',
        );

        $this->call('admin:generate:controller', [
            'table_name' => $tableNameArgument,
            'class_name' => $controllerOption,
            '--model-name' => $modelOption,
            '--template' => 'admin-user',
            '--belongs-to-many' => 'roles',
            '--model-with-full-namespace' => $modelWithFullNamespace,
            '--with-export' => $exportOption,
        ]);

        $this->call('admin:generate:request:index', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
        ]);

        $this->call('admin:generate:request:store', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--template' => 'admin-user',
            '--belongs-to-many' => 'roles',
        ]);

        $this->call('admin:generate:request:update', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--template' => 'admin-user',
            '--belongs-to-many' => 'roles',
        ]);

        $this->call('admin:generate:request:destroy', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
        ]);

        $this->call('admin:generate:request:impersonal-login', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
        ]);

        $this->call('admin:generate:routes', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--controller-name' => $controllerOption,
            '--template' => 'admin-user',
            '--with-export' => $exportOption,
        ]);

        $this->call('admin:generate:index', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--template' => 'admin-user',
            '--with-export' => $exportOption,
        ]);

        $this->call('admin:generate:form', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--belongs-to-many' => 'roles',
            '--template' => 'admin-user',
        ]);

        $this->call('admin:generate:lang', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--template' => 'admin-user',
            '--belongs-to-many' => 'roles',
            '--with-export' => $exportOption,
        ]);

        $this->call('admin:generate:factory', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--template' => 'admin-user',
            '--model-with-full-namespace' => $modelWithFullNamespace,
            '--force' => $force,
        ]);

        if ($exportOption) {
            $this->call('admin:generate:export', [
                'table_name' => $tableNameArgument,
                '--model-with-full-namespace' => $modelWithFullNamespace,
            ]);
        }

        if ($this->option('seed')) {
            $this->info('Seeding testing data');
            $modelWithFullNamespace::factory()->count(20)->create();
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
}
