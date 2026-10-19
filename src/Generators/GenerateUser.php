<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators;

use Brackets\AdminGenerator\Dtos\GeneratedActions;
use Brackets\AdminGenerator\Enums\GeneratedAction;
use Brackets\AdminGenerator\Generators\Traits\FileManipulations;
use Brackets\AdminGenerator\Generators\Traits\ResolvesGeneratedActions;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Override;
use Symfony\Component\Console\Input\InputOption;

final class GenerateUser extends Command
{
    use FileManipulations;
    use ResolvesGeneratedActions;

    private const string TABLE_NAME = 'users';

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
        $modelName = $this->option('model-name');
        $controllerName = $this->option('controller-name');
        $force = $this->option('force');
        $belongsToMany = $this->resolveBelongsToMany();
        $actionOptions = [
            '--only' => $this->option('only'),
            '--except' => $this->option('except'),
            '--with-export' => $this->option('with-export'),
            '--without-bulk' => $this->option('without-bulk'),
        ];
        $translatable = $this->option('translatable');
        $media = $this->option('media');
        $generateModel = $this->option('generate-model');
        $actions = $this->resolveGeneratedActions();

        if ($force) {
            $this->deletePreviousFiles($actions, $generateModel);
        }

        if ($generateModel) {
            $this->call('admin:generate:model', [
                'table_name' => self::TABLE_NAME,
                'class_name' => $modelName,
                '--force' => $force,
                '--template' => 'user',
                '--belongs-to-many' => $belongsToMany,
                '--translatable' => $translatable,
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
            'table_name' => self::TABLE_NAME,
            'class_name' => $controllerName,
            '--model-name' => $modelName,
            '--template' => 'user',
            '--belongs-to-many' => $belongsToMany,
            '--translatable' => $translatable,
            ...$actionOptions,
            '--media' => $media,
        ]);

        $this->generateRequests($actions, $modelName, $force, $belongsToMany, $translatable);

        $this->call('admin:generate:routes', [
            'table_name' => self::TABLE_NAME,
            '--model-name' => $modelName,
            '--controller-name' => $controllerName,
            '--template' => 'user',
            ...$actionOptions,
        ]);

        $this->generateViews($actions, $modelName, $force, $belongsToMany, $translatable, $media, $actionOptions);

        $this->call('admin:generate:lang', [
            'table_name' => self::TABLE_NAME,
            '--model-name' => $modelName,
            '--template' => 'user',
            '--belongs-to-many' => $belongsToMany,
            ...$actionOptions,
            '--media' => $media,
            '--translatable' => $translatable,
        ]);

        $this->call('admin:generate:factory', [
            'table_name' => self::TABLE_NAME,
            '--model-name' => $modelName,
            '--force' => $force,
            '--template' => 'user',
            '--translatable' => $translatable,
        ]);

        if ($actions->has(GeneratedAction::Export)) {
            $this->call('admin:generate:export', [
                'table_name' => self::TABLE_NAME,
                '--force' => $force,
                '--translatable' => $translatable,
            ]);
        }

        if ($this->shouldGeneratePermissionsMigration() || $this->option('force-permissions')) {
            $this->call('admin:generate:permissions', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--force' => $force,
                '--without-bulk' => $actionOptions['--without-bulk'],
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
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
            ['ignore-roles', null, InputOption::VALUE_NONE, 'Do not add the default roles belongs-to-many relation'],
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
            ['generate-model', 'g', InputOption::VALUE_NONE, 'Generates model'],
            [
                'force-permissions',
                null,
                InputOption::VALUE_NONE,
                'Force generating permissions migration even if the Craftable service provider is not installed',
            ],
        ];
    }

    private function generateRequests(
        GeneratedActions $actions,
        ?string $modelName,
        bool $force,
        ?string $belongsToMany,
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
                '--template' => 'user',
                '--belongs-to-many' => $belongsToMany,
                '--translatable' => $translatable,
            ]);
        }

        if ($actions->has(GeneratedAction::Edit)) {
            $this->call('admin:generate:request:update', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--force' => $force,
                '--template' => 'user',
                '--belongs-to-many' => $belongsToMany,
                '--translatable' => $translatable,
            ]);
        }

        if ($actions->has(GeneratedAction::Delete)) {
            $this->call('admin:generate:request:destroy', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--force' => $force,
            ]);
        }

        if ($actions->has(GeneratedAction::BulkDelete)) {
            $this->call('admin:generate:request:bulk-destroy', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--force' => $force,
            ]);
        }

        if ($actions->has(GeneratedAction::Export)) {
            $this->call('admin:generate:request:export', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--force' => $force,
            ]);
        }

        $this->call('admin:generate:request:impersonal-login', [
            'table_name' => self::TABLE_NAME,
            '--model-name' => $modelName,
            '--force' => $force,
        ]);
    }

    /**
     * @param array<string> $media
     * @param array<string, string|bool|null> $actionOptions
     */
    private function generateViews(
        GeneratedActions $actions,
        ?string $modelName,
        bool $force,
        ?string $belongsToMany,
        ?string $translatable,
        array $media,
        array $actionOptions,
    ): void {
        $this->call('admin:generate:blade-index', [
            'table_name' => self::TABLE_NAME,
            '--model-name' => $modelName,
            '--force' => $force,
            '--template' => 'user',
            ...$actionOptions,
            '--translatable' => $translatable,
        ]);

        $this->call('admin:generate:vue-listing', [
            'table_name' => self::TABLE_NAME,
            '--model-name' => $modelName,
            '--force' => $force,
            '--template' => 'user',
            ...$actionOptions,
            '--translatable' => $translatable,
        ]);

        if ($actions->has(GeneratedAction::Show)) {
            $this->call('admin:generate:blade-show', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--force' => $force,
                '--translatable' => $translatable,
            ]);

            $this->call('admin:generate:vue-show', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--force' => $force,
                '--translatable' => $translatable,
            ]);
        }

        if ($actions->has(GeneratedAction::Create)) {
            $this->call('admin:generate:blade-create', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--force' => $force,
                '--template' => 'user',
                '--belongs-to-many' => $belongsToMany,
                '--translatable' => $translatable,
                '--media' => $media,
            ]);
        }

        if ($actions->has(GeneratedAction::Edit)) {
            $this->call('admin:generate:blade-edit', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--force' => $force,
                '--template' => 'user',
                '--belongs-to-many' => $belongsToMany,
                '--translatable' => $translatable,
                '--media' => $media,
            ]);
        }

        if ($actions->has(GeneratedAction::Create) || $actions->has(GeneratedAction::Edit)) {
            $this->call('admin:generate:vue-form', [
                'table_name' => self::TABLE_NAME,
                '--model-name' => $modelName,
                '--force' => $force,
                '--template' => 'user',
                '--belongs-to-many' => $belongsToMany,
                '--translatable' => $translatable,
                '--media' => $media,
            ]);
        }
    }

    private function resolveBelongsToMany(): ?string
    {
        $belongsToMany = $this->option('belongs-to-many');

        // Keep backward compatibility: unless explicitly ignored, the user model always gets the `roles` relation.
        if ($this->option('ignore-roles')) {
            return $belongsToMany;
        }

        $relations = (new Collection($belongsToMany !== null ? explode(',', $belongsToMany) : []))
            ->map(static fn (string $relation): string => trim($relation))
            ->filter();
        if (!$relations->contains('roles')) {
            $relations->push('roles');
        }

        return $relations->implode(',');
    }

    private function deletePreviousFiles(GeneratedActions $actions, bool $generateModel): void
    {
        if ($generateModel) {
            $this->files->delete($this->laravel->path('Models/User.php'));
        }
        if ($actions->has(GeneratedAction::Export)) {
            $this->files->delete($this->laravel->path('Exports/UsersExport.php'));
        }
        $this->files->delete($this->laravel->path('Http/Controllers/Admin/UsersController.php'));
        $this->files->deleteDirectory($this->laravel->path('Http/Requests/Admin/User'));
        $this->files->deleteDirectory($this->laravel->resourcePath('js/admin/user'));
        $this->files->deleteDirectory($this->laravel->resourcePath('views/admin/user'));

        $this->info('Deleting previous files finished.');
    }

    private function shouldGeneratePermissionsMigration(): bool
    {
        return class_exists('\Brackets\Craftable\CraftableServiceProvider');
    }
}
