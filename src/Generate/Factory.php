<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate;

use Symfony\Component\Console\Input\InputOption;

class Factory extends ClassGenerator
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
    protected $description = 'Generate a new factory class';

    /**
     * Path for view
     */
    protected string $view = 'factory';

    public function handle(): void
    {
        $force = $this->option('force');

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        $template = $this->option('template');
        if ($template !== null) {
            $this->view = 'templates.' . $template . '.factory';
        }

        if ($this->generateClass($force)) {
            $this->info('Generating ' . $this->classFullName . ' finished');
        }

        if ($this->option('seed')) {
            $this->info('Seeding testing data');
            $this->modelFullName::factory()->count(50)->create();
        }
    }

    /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
    public function generateClassNameFromTable(string $tableName): string
    {
        return $this->modelBaseName . 'Factory';
    }

    /**
     * Get the root namespace for the class.
     */
    public function rootNamespace(): string
    {
        return 'Database';
    }

    protected function buildClass(): string
    {
        return view(
            'brackets/admin-generator::' . $this->view,
            [
                'modelFullName' => $this->modelFullName,
                'modelBaseName' => $this->modelBaseName,
                'namespace' => $this->classNamespace,

                'columns' => $this->readColumnsFromTable($this->tableName)
                    // we skip primary key
                    ->filter(static fn (array $column): bool => $column['name'] !== 'id')
                    ->map(fn (array $column): array => [
                            'name' => $column['name'],
                            'faker' => $this->getType($column),
                        ]),
                'translatable' => $this->readColumnsFromTable($this->tableName)
                    ->filter(static fn (array $column): bool => $column['majorType'] === 'json')
                    ->pluck('name'),
            ],
        )->render();
    }

    /** @return array<array<string|int>> */
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['seed', 's', InputOption::VALUE_OPTIONAL, 'Seeds the table with fake data'],
            ['model-with-full-namespace', 'fnm', InputOption::VALUE_OPTIONAL, 'Specify model with full namespace'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating factory'],
        ];
    }

    /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return 'Database\Factories';
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

        $type = match ($column['name']) {
            'email' => '$this->faker->email',
            'name',
            'first_name' => '$this->faker->firstName',
            'surname',
            'last_name' => '$this->faker->lastName',
            'slug' => '$this->faker->unique()->slug',
            'password' => 'bcrypt($this->faker->password)',
            default => null,
        };

        if ($type !== null) {
            return $type;
        }

        return match ($column['majorType']) {
            'date' => '$this->faker->date()',
            'time' => '$this->faker->time()',
            'datetime' => '$this->faker->dateTime',
            'text' => '$this->faker->text()',
            'bool' => '$this->faker->boolean()',
            'integer' => '$this->faker->randomNumber(5)',
            'float' => '$this->faker->randomFloat(2)',
            default => '$this->faker->sentence',
        };
    }
}
