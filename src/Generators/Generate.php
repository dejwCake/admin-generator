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
        $tableNameArgument = $this->argument('table_name');
        $modelNameOption = $this->option('model-name');
        $controllerNameOption = $this->option('controller-name');
        $forceOption = $this->option('force');
        $seedOption = $this->option('seed');
        $withExportOption = $this->option('with-export');
        $withoutBulkOption = $this->option('without-bulk');
        $forcePermissionsOption = $this->option('force-permissions');
        $belongsToManyOption = $this->option('belongs-to-many');
        $mediaOption = $this->option('media');

        $this->call('admin:generate:model', [
            'table_name' => $tableNameArgument,
            'class_name' => $modelNameOption,
            '--force' => $forceOption,
            '--belongs-to-many' => $belongsToManyOption,
            '--media' => $mediaOption,
        ]);

        $this->call('admin:generate:factory', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--force' => $forceOption,
            '--seed' => $seedOption,
        ]);

        $this->call('admin:generate:controller', [
            'table_name' => $tableNameArgument,
            'class_name' => $controllerNameOption,
            '--model-name' => $modelNameOption,
            '--force' => $forceOption,
            '--with-export' => $withExportOption,
            '--without-bulk' => $withoutBulkOption,
            '--belongs-to-many' => $belongsToManyOption,
            '--media' => $mediaOption,
        ]);

        $this->call('admin:generate:request:index', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--force' => $forceOption,
        ]);

        $this->call('admin:generate:request:store', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--force' => $forceOption,
            '--belongs-to-many' => $belongsToManyOption,
        ]);

        $this->call('admin:generate:request:update', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--force' => $forceOption,
            '--belongs-to-many' => $belongsToManyOption,
        ]);

        $this->call('admin:generate:request:destroy', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--force' => $forceOption,
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
            '--with-export' => $withExportOption,
            '--without-bulk' => $withoutBulkOption,
        ]);

        $this->call('admin:generate:index', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--force' => $forceOption,
            '--with-export' => $withExportOption,
            '--without-bulk' => $withoutBulkOption,
        ]);

        $this->call('admin:generate:form', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--force' => $forceOption,
            '--belongs-to-many' => $belongsToManyOption,
        ]);

        $this->call('admin:generate:lang', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--with-export' => $withExportOption,
            '--belongs-to-many' => $belongsToManyOption,
        ]);

        if ($withExportOption) {
            $this->call('admin:generate:export', [
                'table_name' => $tableNameArgument,
                '--force' => $forceOption,
            ]);
        }

        if ($forcePermissionsOption || $this->shouldGeneratePermissionsMigration()) {
            $this->call('admin:generate:permissions', [
                'table_name' => $tableNameArgument,
                '--model-name' => $modelNameOption,
                '--force' => $forceOption,
                '--without-bulk' => $withoutBulkOption,
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

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Specify custom model name'],
            ['controller-name', 'c', InputOption::VALUE_OPTIONAL, 'Specify custom controller name'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating admin'],
            ['seed', 's', InputOption::VALUE_NONE, 'Seeds the table with fake data'],
            ['with-export', 'e', InputOption::VALUE_NONE, 'Generate an option to Export as Excel'],
            ['without-bulk', 'wb', InputOption::VALUE_NONE, 'Generate without bulk options'],
            ['force-permissions', 'fp', InputOption::VALUE_NONE, 'Force permission will generate permission migration'],
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
            [
                'media',
                'M',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Media collections (format: name:type:disk:maxFiles)',
            ],
        ];
    }

    private function shouldGeneratePermissionsMigration(): bool
    {
        return class_exists('\Brackets\Craftable\CraftableServiceProvider');
    }
}
