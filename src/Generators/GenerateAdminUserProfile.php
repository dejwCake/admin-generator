<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators;

use Brackets\AdminGenerator\Generators\Traits\FileManipulations;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Override;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class GenerateAdminUserProfile extends Command
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
        $tableName = $this->argument('table_name') ?: 'admin_users';
        $modelName = $this->option('model-name');
        $controllerName = $this->option('controller-name') ?: 'ProfileController';
        $force = $this->option('force');

        if ($force) {
            //remove all files
            $this->files->delete(app_path('Http/Controllers/Admin/ProfileController.php'));
            $this->files->deleteDirectory(resource_path('js/admin/profile-edit-profile'));
            $this->files->deleteDirectory(resource_path('js/admin/profile-edit-password'));
            $this->files->deleteDirectory(resource_path('views/admin/profile'));
        }

        $this->call('admin:generate:controller', [
            'table_name' => $tableName,
            'class_name' => $controllerName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--template' => 'profile',
        ]);

        $this->call('admin:generate:routes', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--controller-name' => $controllerName,
            '--template' => 'profile',
        ]);
        // TODO add this route to the dropdown user-menu

        $this->call('admin:generate:full-form', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--template' => 'profile',
            '--file-name' => 'profile/edit-profile',
            '--route' => 'admin/profile',
        ]);

        $this->call('admin:generate:full-form', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--template' => 'profile.password',
            '--file-name' => 'profile/edit-password',
            '--route' => 'admin/password',
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
    #[Override]
    protected function getArguments(): array
    {
        return [
            ['table_name', InputArgument::OPTIONAL, 'Name of the existing table'],
        ];
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Specify custom model name'],
            ['controller-name', 'c', InputOption::VALUE_OPTIONAL, 'Specify custom controller name'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating admin profile'],
        ];
    }
}
