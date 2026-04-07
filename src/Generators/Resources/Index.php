<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Override;
use Symfony\Component\Console\Input\InputOption;

final class Index extends ResourceGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:index';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate index view and listing Vue component';

    public function handle(): void
    {
        $this->call('admin:generate:blade-index', [
            'table_name' => $this->tableName,
            '--model-name' => $this->option('model-name'),
            '--force' => $this->option('force'),
            '--template' => $this->option('template'),
            '--with-export' => $this->option('with-export'),
            '--without-bulk' => $this->option('without-bulk'),
        ]);

        $this->call('admin:generate:vue-listing', [
            'table_name' => $this->tableName,
            '--model-name' => $this->option('model-name'),
            '--force' => $this->option('force'),
            '--template' => $this->option('template'),
            '--with-export' => $this->option('with-export'),
            '--without-bulk' => $this->option('without-bulk'),
        ]);
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating index'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['with-export', 'e', InputOption::VALUE_NONE, 'Generate an option to Export as Excel'],
            ['without-bulk', 'wb', InputOption::VALUE_NONE, 'Generate without bulk options'],
        ];
    }
}
