<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate;

use Symfony\Component\Console\Input\InputOption;

class ModelFactory extends FileAppender
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:factory';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Append a new factory';

    /**
     * Path for view
     */
    protected string $view = 'factory';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        $template = $this->option('template');
        if ($template !== null) {
            $this->view = 'templates.' . $template . '.factory';
        }

        if ($this->appendIfNotAlreadyAppended(base_path('database/factories/ModelFactory.php'), $this->buildClass())) {
            $this->info('Appending ' . $this->modelBaseName . ' model to ModelFactory finished');
        }

        if ($this->option('seed')) {
            $this->info('Seeding testing data');
            $this->modelFullName::factory()->count(50)->create();
        }
    }

    protected function buildClass(): string
    {
        return view('brackets/admin-generator::' . $this->view, [
            'modelFullName' => $this->modelFullName,

            'columns' => $this->readColumnsFromTable($this->tableName)
                // we skip primary key
                ->filter(static fn ($column) => $column['name'] !== 'id')
                ->map(fn ($column) => [
                        'name' => $column['name'],
                        'faker' => $this->getType($column),
                    ]),
            'translatable' => $this->readColumnsFromTable($this->tableName)->filter(
                static fn ($column) => $column['type'] === "json",
            )->pluck(
                'name',
            ),
        ])->render();
    }

    /** @return array<array<string|int>> */
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['seed', 's', InputOption::VALUE_OPTIONAL, 'Seeds the table with fake data'],
            ['model-with-full-namespace', 'fnm', InputOption::VALUE_OPTIONAL, 'Specify model with full namespace'],
        ];
    }

    /**
     * @param array<string, string|bool> $column
     */
    private function getType(array $column): string
    {
        if ($column['name'] === 'deleted_at') {
            return 'null';
        }

        if ($column['name'] === 'remember_token') {
            return 'null';
        }

        $type = match ($column['type']) {
            'date' => '$faker->date()',
            'time' => '$faker->time()',
            'datetime' => '$faker->dateTime',
            'text' => '$faker->text()',
            'boolean' => '$faker->boolean()',
            'integer',
            'numeric',
            'decimal' => '$faker->randomNumber(5)',
            'float' => '$faker->randomFloat',
            default => null,
        };

        if ($type !== null) {
            return $type;
        }

        $type = match ($column['name']) {
            'title' => '$faker->sentence',
            'email' => '$faker->email',
            'name',
            'first_name' => '$faker->firstName',
            'surname',
            'last_name' => '$faker->lastName',
            'slug' => '$faker->unique()->slug',
            'password' => 'bcrypt($faker->password)',
            default => '$faker->sentence',
        };

        if ($type !== null) {
            return $type;
        }

        return '$faker->sentence';
    }
}
