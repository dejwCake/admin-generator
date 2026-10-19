<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators;

use Brackets\AdminGenerator\Dtos\GeneratedActions;
use Brackets\AdminGenerator\Enums\GeneratedAction;
use Brackets\AdminGenerator\Generators\Traits\FileManipulations;
use Brackets\AdminGenerator\Generators\Traits\ResolvesGeneratedActions;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Override;
use Symfony\Component\Console\Input\InputOption;

final class GenerateAdminUser extends Command
{
    use FileManipulations;
    use ResolvesGeneratedActions;

    private const string TABLE_NAME = 'admin_users';

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
        $modelName = $this->resolveModelName();
        $modelWithFullNamespace = $this->resolveModelWithFullNamespace();
        $controllerName = $this->option('controller-name');
        $force = $this->option('force');
        $actionOptions = [
            '--only' => $this->option('only'),
            '--except' => $this->option('except'),
            '--with-export' => $this->option('with-export'),
            '--without-bulk' => $this->option('without-bulk'),
        ];
        $translatable = $this->option('translatable');
        $media = $this->option('media');
        $actions = $this->resolveGeneratedActions();

        if ($force) {
            $this->deletePreviousFiles($actions);
        }

        // we need to replace this before controller generation happens
        $this->strReplaceInFile(
            $this->laravel->resourcePath('views/admin/layout/sidebar.blade.php'),
            '{{-- Do not delete me :) I\'m also used for auto-generation menu items --}}',
            '<li class="nav-item"><a class="nav-link" href="{{ url(\'admin/admin-users\') }}"><i class="nav-icon fa fa-user"></i> {{ __(\'Manage access\') }}</a></li>
        {{-- Do not delete me :) I\'m also used for auto-generation menu items --}}',
            '|url\(\'admin\/admin-users\'\)|',
        );

        $this->call('admin:generate:controller', [
            'table_name' => self::TABLE_NAME,
            'class_name' => $controllerName,
            '--model-name' => $modelName,
            '--model-with-full-namespace' => $modelWithFullNamespace,
            '--template' => 'admin-user',
            '--belongs-to-many' => 'roles',
            '--translatable' => $translatable,
            ...$actionOptions,
            '--media' => $media,
        ]);

        $this->generateRequests($actions, $modelName, $modelWithFullNamespace, $force, $translatable);

        $this->call('admin:generate:routes', [
            'table_name' => self::TABLE_NAME,
            '--model-name' => $modelName,
            '--controller-name' => $controllerName,
            '--template' => 'admin-user',
            ...$actionOptions,
        ]);

        $this->generateViews($actions, $modelName, $force, $translatable, $media, $actionOptions);

        $this->call('admin:generate:lang', [
            'table_name' => self::TABLE_NAME,
            '--model-name' => $modelName,
            '--belongs-to-many' => 'roles',
            ...$actionOptions,
            '--media' => $media,
            '--translatable' => $translatable,
        ]);

        $this->call('admin:generate:factory', [
            'table_name' => self::TABLE_NAME,
            '--model-name' => $modelName,
            '--model-with-full-namespace' => $modelWithFullNamespace,
            '--force' => $force,
            '--translatable' => $translatable,
        ]);

        if ($actions->has(GeneratedAction::Export)) {
            $this->call('admin:generate:export', [
                'table_name' => self::TABLE_NAME,
                '--model-with-full-namespace' => $modelWithFullNamespace,
                '--force' => $force,
                '--translatable' => $translatable,
            ]);
        }

        if ($this->option('seed')) {
            $this->info('Seeding testing data');
            $modelWithFullNamespace::factory()->count(20)->create();
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
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating admin user'],
            ...$this->getGeneratedActionOptions(),
            [
                'translatable',
                'tr',
                InputOption::VALUE_OPTIONAL,
                'Comma-separated list of columns to treat as translatable (defaults to all json/jsonb columns)',
            ],
            [
                'media',
                'M',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Media collections (format: name:type:disk:maxFiles[:maxFileSizeInMb])',
            ],
            ['seed', 's', InputOption::VALUE_NONE, 'Seeds table with fake data'],
        ];
    }

    private function generateRequests(
        GeneratedActions $actions,
        string $modelName,
        ?string $modelWithFullNamespace,
        bool $force,
        ?string $translatable,
    ): void {
        $this->call('admin:generate:request:index', [
            'table_name' => self::TABLE_NAME,
            '--model-name' => $modelName,
            '--force' => $force,
            '--translatable' => $translatable,
        ]);

        if ($actions->has(GeneratedAction::Create)) {
            $this->call('admin:generate:request:store', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--force' => $force,
                '--template' => 'admin-user',
                '--belongs-to-many' => 'roles',
                '--translatable' => $translatable,
            ]);
        }

        if ($actions->has(GeneratedAction::Edit)) {
            $this->call('admin:generate:request:update', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--model-with-full-namespace' => $modelWithFullNamespace,
                '--force' => $force,
                '--template' => 'admin-user',
                '--belongs-to-many' => 'roles',
                '--translatable' => $translatable,
            ]);
        }

        if ($actions->has(GeneratedAction::Delete)) {
            $this->call('admin:generate:request:destroy', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--model-with-full-namespace' => $modelWithFullNamespace,
                '--force' => $force,
            ]);
        }

        if ($actions->has(GeneratedAction::BulkDelete)) {
            $this->call('admin:generate:request:bulk-destroy', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--model-with-full-namespace' => $modelWithFullNamespace,
                '--force' => $force,
            ]);
        }

        if ($actions->has(GeneratedAction::Export)) {
            $this->call('admin:generate:request:export', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--model-with-full-namespace' => $modelWithFullNamespace,
                '--force' => $force,
            ]);
        }

        $this->call('admin:generate:request:impersonal-login', [
            'table_name' => self::TABLE_NAME,
            '--model-name' => $modelName,
            '--model-with-full-namespace' => $modelWithFullNamespace,
            '--force' => $force,
        ]);
    }

    /**
     * @param array<string> $media
     * @param array<string, string|bool|null> $actionOptions
     */
    private function generateViews(
        GeneratedActions $actions,
        string $modelName,
        bool $force,
        ?string $translatable,
        array $media,
        array $actionOptions,
    ): void {
        $this->call('admin:generate:blade-index', [
            'table_name' => self::TABLE_NAME,
            '--model-name' => $modelName,
            '--force' => $force,
            '--template' => 'admin-user',
            ...$actionOptions,
            '--translatable' => $translatable,
        ]);

        $this->call('admin:generate:vue-listing', [
            'table_name' => self::TABLE_NAME,
            '--model-name' => $modelName,
            '--force' => $force,
            '--template' => 'admin-user',
            ...$actionOptions,
            '--translatable' => $translatable,
        ]);

        if ($actions->has(GeneratedAction::Show)) {
            $this->call('admin:generate:blade-show', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--force' => $force,
                '--belongs-to-many' => 'roles',
                '--translatable' => $translatable,
            ]);

            $this->call('admin:generate:vue-show', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--force' => $force,
                '--belongs-to-many' => 'roles',
                '--translatable' => $translatable,
            ]);
        }

        if ($actions->has(GeneratedAction::Create)) {
            $this->call('admin:generate:blade-create', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--force' => $force,
                '--template' => 'admin-user',
                '--belongs-to-many' => 'roles',
                '--translatable' => $translatable,
                '--media' => $media,
            ]);
        }

        if ($actions->has(GeneratedAction::Edit)) {
            $this->call('admin:generate:blade-edit', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--force' => $force,
                '--template' => 'admin-user',
                '--belongs-to-many' => 'roles',
                '--translatable' => $translatable,
                '--media' => $media,
            ]);
        }

        if ($actions->has(GeneratedAction::Create) || $actions->has(GeneratedAction::Edit)) {
            $this->call('admin:generate:vue-form', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--force' => $force,
                '--template' => 'admin-user',
                '--belongs-to-many' => 'roles',
                '--translatable' => $translatable,
                '--media' => $media,
            ]);
        }
    }

    private function resolveModelName(): string
    {
        $modelName = $this->option('model-name');

        return is_string($modelName) ? $modelName : 'AdminUser';
    }

    private function resolveModelWithFullNamespace(): ?string
    {
        return $this->option('model-name') === null ? 'Brackets\AdminAuth\Models\AdminUser' : null;
    }

    private function deletePreviousFiles(GeneratedActions $actions): void
    {
        if ($actions->has(GeneratedAction::Export)) {
            $this->files->delete($this->laravel->path('Exports/AdminUsersExport.php'));
        }
        $this->files->delete($this->laravel->path('Http/Controllers/Admin/AdminUsersController.php'));
        $this->files->delete(
            $this->laravel->databasePath('Factories/Brackets/AdminAuth/Models/AdminUserFactory.php'),
        );
        $this->files->deleteDirectory($this->laravel->path('Http/Requests/Admin/AdminUser'));
        $this->files->deleteDirectory($this->laravel->resourcePath('js/admin/admin-user'));
        $this->files->deleteDirectory($this->laravel->resourcePath('views/admin/admin-user'));

        $this->info('Deleting previous files finished.');
    }
}
