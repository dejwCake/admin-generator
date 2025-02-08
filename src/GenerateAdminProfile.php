<?php

namespace Brackets\AdminGenerator;

use Brackets\AdminGenerator\Generate\Traits\FileManipulations;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class GenerateAdminProfile extends Command
{

    use FileManipulations;

    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:admin-user:profile';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Scaffold admin "My Profile" feature (controller, views, routes)';

    public function __construct(protected readonly Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $tableNameArgument = !empty($this->argument('table_name')) ? $this->argument('table_name') : 'admin_users';
        $modelOption = $this->option('model-name');
        $controllerOption = !empty($this->option('controller-name')) ? $this->option('controller-name') : 'ProfileController';
        $force = $this->option('force');

        if ($force) {
            //remove all files
            $this->files->delete(app_path('Http/Controllers/Admin/ProfileController.php'));
            $this->files->deleteDirectory(resource_path('js/admin/profile-edit-profile'));
            $this->files->deleteDirectory(resource_path('js/admin/profile-edit-password'));
            $this->files->deleteDirectory(resource_path('views/admin/profile'));
        }

        $this->call('admin:generate:controller', [
            'table_name' => $tableNameArgument,
            'class_name' => $controllerOption,
            '--model-name' => $modelOption,
            '--template' => 'profile',
        ]);

        $this->call('admin:generate:routes', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--controller-name' => $controllerOption,
            '--template' => 'profile',
        ]);
        // TODO add this route to the dropdown user-menu

        $this->call('admin:generate:full-form', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--file-name' => 'profile/edit-profile',
            '--route' => 'admin/profile',
            '--template' => 'profile',
        ]);

        $this->call('admin:generate:full-form', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelOption,
            '--file-name' => 'profile/edit-password',
            '--route' => 'admin/password',
            '--template' => 'profile.password',
        ]);

        $this->strReplaceInFile(
            resource_path('views/admin/layout/profile-dropdown.blade.php'),
            '{{-- Do not delete me :) I\'m used for auto-generation menu items --}}',
            '<a href="{{ url(\'admin/profile\') }}" class="dropdown-item"><i class="fa fa-user"></i>  {{ trans(\'brackets/admin-auth::admin.profile_dropdown.profile\') }}</a>
    {{-- Do not delete me :) I\'m used for auto-generation menu items --}}',
            '|url\(\'admin\/profile\'\)|',
        );

        $this->strReplaceInFile(
            resource_path('views/admin/layout/profile-dropdown.blade.php'),
            '{{-- Do not delete me :) I\'m used for auto-generation menu items --}}',
            '<a href="{{ url(\'admin/password\') }}" class="dropdown-item"><i class="fa fa-key"></i>  {{ trans(\'brackets/admin-auth::admin.profile_dropdown.password\') }}</a>
    {{-- Do not delete me :) I\'m used for auto-generation menu items --}}',
            '|url\(\'admin\/password\'\)|',
        );

        $this->info('Generating whole admin "My Profile" finished');
    }

    /** @return array<array<string|int>> */
    protected function getArguments(): array
    {
        return [
            ['table_name', InputArgument::OPTIONAL, 'Name of the existing table'],
        ];
    }

    /** @return array<array<string|int>> */
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Specify custom model name'],
            ['controller-name', 'c', InputOption::VALUE_OPTIONAL, 'Specify custom controller name'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating admin profile'],
        ];
    }
}
