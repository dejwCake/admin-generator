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

        $this->call('admin:generate:model', [
            'table_name' => $tableNameArgument,
            'class_name' => $modelNameOption,
            '--force' => $forceOption,
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
        ]);

        $this->call('admin:generate:request:update', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--force' => $forceOption,
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
        ]);

        $this->call('admin:generate:lang', [
            'table_name' => $tableNameArgument,
            '--model-name' => $modelNameOption,
            '--with-export' => $withExportOption,
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
        ];
    }

    private function shouldGeneratePermissionsMigration(): bool
    {
        return class_exists('\Brackets\Craftable\CraftableServiceProvider');
    }
}


/**
 * TODO test belongs_to_many in all generators
 *
 * TODO add template to all + it can be relative or absolute path
 *
 * Admin: seed, controller_name, model_name
 *
 * Model: class_name (App\Models), template, belongs_to_many
 *
 * Controller: class_name (App\Http\Controllers\Admin), model_name, template, belongs_to_many
 *
 * StoreRequest: class_name (App\Http\Requests\Admin\{model_name}), model_name
 *
 * UpdateRequest: class_name (App\Http\Requests\Admin\{model_name}), model_name
 *
 * TODO add DestroyRequest
 * DestroyRequest: class_name (App\Http\Requests\Admin\{model_name}), model_name
 *
 *
 * Appendor:
 *
 * ModelFactory: model_name
 *
 * Routes: model_name, controller_name, template
 *
 *
 * ViewGenerator:
 *
 * ViewForm: file_name, model_name, belongs_to_many
 *
 * TODO refactor ViewFullForm generator
 * ViewFullForm: file_name, model_name, template, name, view_name, route
 *
 * ViewIndex: file_name, model_name, template
 */
