<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Override;
use Symfony\Component\Console\Input\InputOption;

final class Form extends ResourceGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:form';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate create and edit view templates';

    public function handle(): void
    {
        $this->call('admin:generate:blade-create', [
            'table_name' => $this->tableName,
            '--model-name' => $this->option('model-name'),
            '--force' => $this->option('force'),
            '--template' => $this->option('template'),
            '--belongs-to-many' => $this->option('belongs-to-many'),
            '--media' => $this->option('media'),
        ]);

        $this->call('admin:generate:blade-edit', [
            'table_name' => $this->tableName,
            '--model-name' => $this->option('model-name'),
            '--force' => $this->option('force'),
            '--template' => $this->option('template'),
            '--belongs-to-many' => $this->option('belongs-to-many'),
            '--media' => $this->option('media'),
        ]);

        $this->call('admin:generate:vue-form', [
            'table_name' => $this->tableName,
            '--model-name' => $this->option('model-name'),
            '--force' => $this->option('force'),
            '--template' => $this->option('template'),
            '--belongs-to-many' => $this->option('belongs-to-many'),
            '--media' => $this->option('media'),
        ]);
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating form'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
            [
                'media',
                'M',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Media collections (format: name:type:disk:maxFiles)',
            ],
        ];
    }
}
