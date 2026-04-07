<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Override;
use Symfony\Component\Console\Input\InputOption;

final class FullForm extends ResourceGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:full-form';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate a full-form view template';

    public function handle(): void
    {
        $this->call('admin:generate:blade-form', [
            'table_name' => $this->tableName,
            '--model-name' => $this->option('model-name'),
            '--force' => $this->option('force'),
            '--template' => $this->option('template'),
            '--file-name' => $this->option('file-name'),
            '--route' => $this->option('route'),
            '--belongs-to-many' => $this->option('belongs-to-many'),
        ]);

        $this->call('admin:generate:vue-form', [
            'table_name' => $this->tableName,
            '--model-name' => $this->option('model-name'),
            '--force' => $this->option('force'),
            '--template' => $this->option('template'),
            '--file-name' => $this->option('file-name'),
            '--belongs-to-many' => $this->option('belongs-to-many'),
        ]);
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating full form'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['file-name', 'nm', InputOption::VALUE_OPTIONAL, 'Specify a blade file path'],
            ['route', 'r', InputOption::VALUE_OPTIONAL, 'Specify custom route for form'],
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
        ];
    }
}
