<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators;

use Brackets\AdminGenerator\Dtos\GeneratedActions;
use Brackets\AdminGenerator\Enums\GeneratedAction;
use Brackets\AdminGenerator\Generators\Traits\ResolvesGeneratedActions;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Override;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class Generate extends Command
{
    use ResolvesGeneratedActions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Scaffold complete CRUD admin interface';

    public function __construct(protected readonly Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $tableName = $this->argument('table_name');
        $modelName = $this->option('model-name');
        $controllerName = $this->option('controller-name');
        $force = $this->option('force');
        $belongsToMany = $this->option('belongs-to-many');
        $translatable = $this->option('translatable');
        $actionOptions = array_filter(
            [
                '--only' => $this->option('only'),
                '--except' => $this->option('except'),
                '--with-export' => $this->option('with-export'),
                '--without-bulk' => $this->option('without-bulk'),
            ],
            static fn (mixed $value): bool => $value !== null,
        );
        $media = $this->option('media');
        $actions = $this->resolveGeneratedActions();

        $this->generateModel($tableName, $modelName, $force, $belongsToMany, $translatable, $media);
        $this->generateFactory($tableName, $modelName, $force, $translatable);

        $this->call('admin:generate:controller', [
            'table_name' => $tableName,
            'class_name' => $controllerName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--belongs-to-many' => $belongsToMany,
            '--translatable' => $translatable,
            ...$actionOptions,
            '--media' => $media,
        ]);

        $this->generateRequests($actions, $tableName, $modelName, $force, $belongsToMany, $translatable);

        $this->call('admin:generate:routes', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--controller-name' => $controllerName,
            ...$actionOptions,
        ]);

        $this->generateViews(
            $actions,
            $tableName,
            $modelName,
            $force,
            $belongsToMany,
            $translatable,
            $media,
            $actionOptions,
        );

        $this->call('admin:generate:lang', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--belongs-to-many' => $belongsToMany,
            ...$actionOptions,
            '--media' => $media,
            '--translatable' => $translatable,
        ]);

        if ($actions->has(GeneratedAction::Export)) {
            $this->call('admin:generate:export', [
                'table_name' => $tableName,
                '--force' => $force,
                '--translatable' => $translatable,
            ]);
        }

        if ($this->shouldGeneratePermissionsMigration() || $this->option('force-permissions')) {
            $this->call('admin:generate:permissions', [
                'table_name' => $tableName,
                '--model-name' => $modelName,
                '--force' => $force,
                '--without-bulk' => $actionOptions['--without-bulk'],
            ]);

            if (
                $this->option('no-interaction')
                || $this->confirm('Do you want to attach generated permissions to the default role now?', true)
            ) {
                $this->call('migrate');
            }
        }

        $this->info('Generating whole admin finished');
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getArguments(): array
    {
        return [
            ['table_name', InputArgument::REQUIRED, 'Name of the existing table'],
        ];
    }

    /** @return array<array<string|int|null>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Specify custom model name'],
            ['controller-name', 'c', InputOption::VALUE_OPTIONAL, 'Specify custom controller name'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating admin'],
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
            [
                'translatable',
                'tr',
                InputOption::VALUE_OPTIONAL,
                'Comma-separated list of columns to treat as translatable (defaults to all json/jsonb columns)',
            ],
            ...$this->getGeneratedActionOptions(),
            ['without-model', 'wm', InputOption::VALUE_NONE, 'Skip generating the model class (use an existing one)'],
            [
                'without-factory',
                'wf',
                InputOption::VALUE_NONE,
                'Skip generating the factory class (use an existing one)',
            ],
            [
                'media',
                'M',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Media collections (format: name:type:disk:maxFiles[:maxFileSizeInMb])',
            ],
            ['seed', 's', InputOption::VALUE_NONE, 'Seeds the table with fake data'],
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
        string $tableName,
        ?string $modelName,
        bool $force,
        ?string $belongsToMany,
        ?string $translatable,
    ): void {
        $this->call('admin:generate:request:index', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--translatable' => $translatable,
        ]);

        if ($actions->has(GeneratedAction::Create)) {
            $this->call('admin:generate:request:store', [
                'table_name' => $tableName,
                '--model-name' => $modelName,
                '--force' => $force,
                '--belongs-to-many' => $belongsToMany,
                '--translatable' => $translatable,
            ]);
        }

        if ($actions->has(GeneratedAction::Edit)) {
            $this->call('admin:generate:request:update', [
                'table_name' => $tableName,
                '--model-name' => $modelName,
                '--force' => $force,
                '--belongs-to-many' => $belongsToMany,
                '--translatable' => $translatable,
            ]);
        }

        if ($actions->has(GeneratedAction::Delete)) {
            $this->call('admin:generate:request:destroy', [
                'table_name' => $tableName,
                '--model-name' => $modelName,
                '--force' => $force,
            ]);
        }

        if ($actions->has(GeneratedAction::BulkDelete)) {
            $this->call('admin:generate:request:bulk-destroy', [
                'table_name' => $tableName,
                '--model-name' => $modelName,
                '--force' => $force,
            ]);
        }

        if ($actions->has(GeneratedAction::Export)) {
            $this->call('admin:generate:request:export', [
                'table_name' => $tableName,
                '--model-name' => $modelName,
                '--force' => $force,
            ]);
        }
    }

    /**
     * @param array<string> $media
     * @param array<string, string|bool> $actionOptions
     */
    private function generateViews(
        GeneratedActions $actions,
        string $tableName,
        ?string $modelName,
        bool $force,
        ?string $belongsToMany,
        ?string $translatable,
        array $media,
        array $actionOptions,
    ): void {
        $this->call('admin:generate:blade-index', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            ...$actionOptions,
            '--translatable' => $translatable,
        ]);

        $this->call('admin:generate:vue-listing', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            ...$actionOptions,
            '--translatable' => $translatable,
        ]);

        if ($actions->has(GeneratedAction::Show)) {
            $this->call('admin:generate:blade-show', [
                'table_name' => $tableName,
                '--model-name' => $modelName,
                '--force' => $force,
                '--belongs-to-many' => $belongsToMany,
                '--translatable' => $translatable,
            ]);

            $this->call('admin:generate:vue-show', [
                'table_name' => $tableName,
                '--model-name' => $modelName,
                '--force' => $force,
                '--belongs-to-many' => $belongsToMany,
                '--translatable' => $translatable,
            ]);
        }

        if ($actions->has(GeneratedAction::Create)) {
            $this->call('admin:generate:blade-create', [
                'table_name' => $tableName,
                '--model-name' => $modelName,
                '--force' => $force,
                '--belongs-to-many' => $belongsToMany,
                '--translatable' => $translatable,
                '--media' => $media,
            ]);
        }

        if ($actions->has(GeneratedAction::Edit)) {
            $this->call('admin:generate:blade-edit', [
                'table_name' => $tableName,
                '--model-name' => $modelName,
                '--force' => $force,
                '--belongs-to-many' => $belongsToMany,
                '--translatable' => $translatable,
                '--media' => $media,
            ]);
        }

        if ($actions->has(GeneratedAction::Create) || $actions->has(GeneratedAction::Edit)) {
            $this->call('admin:generate:vue-form', [
                'table_name' => $tableName,
                '--model-name' => $modelName,
                '--force' => $force,
                '--belongs-to-many' => $belongsToMany,
                '--translatable' => $translatable,
                '--media' => $media,
            ]);
        }
    }

    /**
     * @param array<string> $media
     */
    private function generateModel(
        string $tableName,
        ?string $modelName,
        bool $force,
        ?string $belongsToMany,
        ?string $translatable,
        array $media,
    ): void {
        if ($this->option('without-model')) {
            return;
        }

        $this->call('admin:generate:model', [
            'table_name' => $tableName,
            'class_name' => $modelName,
            '--force' => $force,
            '--belongs-to-many' => $belongsToMany,
            '--translatable' => $translatable,
            '--media' => $media,
        ]);
    }

    private function generateFactory(string $tableName, ?string $modelName, bool $force, ?string $translatable): void
    {
        $seed = $this->option('seed');

        if ($this->option('without-factory')) {
            if ($seed) {
                $this->warn('Skipping seeding: --seed requires the factory, which is skipped by --without-factory.');
            }

            return;
        }

        $this->call('admin:generate:factory', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--seed' => $seed,
            '--translatable' => $translatable,
        ]);
    }

    private function shouldGeneratePermissionsMigration(): bool
    {
        return class_exists('\Brackets\Craftable\CraftableServiceProvider');
    }
}
