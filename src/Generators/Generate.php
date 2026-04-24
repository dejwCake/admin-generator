<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Override;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class Generate extends Command
{
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
        $withExport = $this->option('with-export');
        $withoutBulk = $this->option('without-bulk');
        $media = $this->option('media');
        $seed = $this->option('seed');

        $this->call('admin:generate:model', [
            'table_name' => $tableName,
            'class_name' => $modelName,
            '--force' => $force,
            '--belongs-to-many' => $belongsToMany,
            '--media' => $media,
        ]);

        $this->call('admin:generate:factory', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--seed' => $seed,
        ]);

        $this->call('admin:generate:controller', [
            'table_name' => $tableName,
            'class_name' => $controllerName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--belongs-to-many' => $belongsToMany,
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
            '--belongs-to-many' => $belongsToMany,
        ]);

        $this->call('admin:generate:request:update', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--belongs-to-many' => $belongsToMany,
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

        $this->call('admin:generate:routes', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--controller-name' => $controllerName,
            '--with-export' => $withExport,
            '--without-bulk' => $withoutBulk,
        ]);

        $this->call('admin:generate:blade-index', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--with-export' => $withExport,
            '--without-bulk' => $withoutBulk,
        ]);

        $this->call('admin:generate:vue-listing', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--with-export' => $withExport,
            '--without-bulk' => $withoutBulk,
        ]);

        $this->call('admin:generate:blade-create', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--belongs-to-many' => $belongsToMany,
            '--media' => $media,
        ]);

        $this->call('admin:generate:blade-edit', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--belongs-to-many' => $belongsToMany,
            '--media' => $media,
        ]);

        $this->call('admin:generate:vue-form', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--force' => $force,
            '--belongs-to-many' => $belongsToMany,
            '--media' => $media,
        ]);

        $this->call('admin:generate:lang', [
            'table_name' => $tableName,
            '--model-name' => $modelName,
            '--belongs-to-many' => $belongsToMany,
            '--with-export' => $withExport,
            '--media' => $media,
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
            ['with-export', 'e', InputOption::VALUE_NONE, 'Generate an option to Export as Excel'],
            ['without-bulk', 'wb', InputOption::VALUE_NONE, 'Generate without bulk options'],
            [
                'media',
                'M',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Media collections (format: name:type:disk:maxFiles)',
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

    private function shouldGeneratePermissionsMigration(): bool
    {
        return class_exists('\Brackets\Craftable\CraftableServiceProvider');
    }
}
