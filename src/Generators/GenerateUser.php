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
        $tableNameArgument = 'users';
        $modelNameOption = $this->option('model-name');
        $controllerNameOption = $this->option('controller-name');
        $generateModelOption = $this->option('generate-model');
        $forceOption = $this->option('force');
        $withExportOption = $this->option('with-export');
        $withoutBulkOption = $this->option('without-bulk');
        $mediaOption = $this->option('media');

        if ($forceOption) {
            //remove all files
            if ($generateModelOption) {
                $this->files->delete(app_path('Models/User.php'));
            }
            if ($withExportOption) {
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
                'class_name' => $modelNameOption,
                '--template' => 'user',
                '--belongs-to-many' => 'roles',
                '--media' => $mediaOption,
            ]);

            //TODO change config/auth.php to use our user model for auth
        }

        // we need to replace this before controller generation happens
        $this->strReplaceInFile(
            resource_path('views/admin/layout/sidebar.blade.php'),
            '{{-- Do not delete me :) I\'m also used for auto-generation menu items --}}',
            '<li class="nav-item"><a class="nav-link" href="{{ url(\'admin/users\') }}"><i class="nav-icon fa fa-user"></i> {{ __(\'Manage users\') }}</a></li>
            {{-- Do not delete me :) I\'m also used for auto-generation menu items --}}',
            '|url\(\'admin\/users\'\)|',
        );

        $this->call('admin:generate:controller', [
            'table_name' => $tableNameArgument,
            'class_name' => $controllerNameOption,
            '--model-name' => $modelNameOption,
            '--template' => 'user',
            '--with-export' => $withExportOption,
            '--without-bulk' => $withoutBulkOption,
            '--belongs-to-many' => 'roles',
            '--media' => $mediaOption,
        ]);

        $this->call('admin:generate:request:index', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
        ]);

        $this->call('admin:generate:request:store', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--template' => 'user',
            '--belongs-to-many' => 'roles',
        ]);

        $this->call('admin:generate:request:update', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--template' => 'user',
            '--belongs-to-many' => 'roles',
        ]);

        $this->call('admin:generate:request:destroy', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
        ]);

        if (!$withoutBulkOption) {
            $this->call('admin:generate:request:bulk-destroy', [
                'table_name' => $tableNameArgument,
                '--model-name' => $modelNameOption,
                '--force' => $forceOption,
            ]);
        }

        if ($withExportOption) {
            $this->call('admin:generate:request:export', [
                'table_name' => $tableNameArgument,
                '--model-name' => $modelNameOption,
                '--force' => $forceOption,
            ]);
        }

        $this->call('admin:generate:routes', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--controller-name' => $controllerNameOption,
            '--template' => 'user',
            '--with-export' => $withExportOption,
        ]);

        $this->call('admin:generate:index', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--template' => 'user',
            '--with-export' => $withExportOption,
        ]);

        $this->call('admin:generate:form', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--belongs-to-many' => 'roles',
            '--template' => 'user',
        ]);

        $this->call('admin:generate:lang', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--template' => 'user',
            '--belongs-to-many' => 'roles',
            '--with-export' => $withExportOption,
        ]);

        $this->call('admin:generate:factory', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--force' => $forceOption,
        ]);

        if ($withExportOption) {
            $this->call('admin:generate:export', [
                'table_name' => $tableNameArgument,
                '--force' => $forceOption,
            ]);
        }

        if ($this->shouldGeneratePermissionsMigration()) {
            $this->call('admin:generate:permissions', [
                'table_name' => $tableNameArgument,
                '--model-name' => $modelNameOption,
                '--force' => $forceOption,
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
            $modelNameOption::factory()->count(20)->create();
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

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Specify custom model name'],
            ['controller-name', 'c', InputOption::VALUE_OPTIONAL, 'Specify custom controller name'],
            ['generate-model', 'g', InputOption::VALUE_NONE, 'Generates model'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating admin user'],
            ['seed', 's', InputOption::VALUE_NONE, 'Seeds table with fake data'],
            ['with-export', 'e', InputOption::VALUE_NONE, 'Generate an option to Export as Excel'],
            ['without-bulk', 'wb', InputOption::VALUE_NONE, 'Generate without bulk options'],
            ['media', 'M', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Media collections (format: name:type:disk:maxFiles)'],
        ];
    }

    private function shouldGeneratePermissionsMigration(): bool
    {
        return class_exists('\Brackets\Craftable\CraftableServiceProvider');
    }
}
