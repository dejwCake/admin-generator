<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Classes;

use Illuminate\Support\Collection;
use Override;
use Symfony\Component\Console\Input\InputOption;

final class StoreRequest extends ClassGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:request:store';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate a Store request class';

    /**
     * Path for view
     */
    protected string $view = 'store-request';

    public function handle(): void
    {
        $force = $this->option('force');
        $template = $this->option('template');
        $belongsToMany = $this->option('belongs-to-many');

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        if ($template !== null) {
            $this->view = 'templates.' . $template . '.store-request';
        }

        if ($belongsToMany !== null) {
            $this->setBelongToManyRelation($belongsToMany);
        }

        if ($this->generateClass($force)) {
            $this->info('Generating ' . $this->classFullName . ' finished');
        }
    }

    /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
    #[Override]
    public function generateClassNameFromTable(string $tableName): string
    {
        return 'Store' . $this->modelBaseName;
    }

    #[Override]
    protected function buildClass(): string
    {
        $columns = $this->getVisibleColumns($this->tableName, $this->modelVariableName);
        $allColumns = $this->readColumnsFromTable($this->tableName);

        return view(
            'brackets/admin-generator::' . $this->view,
            [
                'classBaseName' => $this->classBaseName,
                'classNamespace' => $this->classNamespace,
                'modelDotNotation' => $this->modelDotNotation,

                'hasBelongsToMany' => count($this->relations) > 0 && count($this->relations['belongsToMany']) > 0,
                'hasRuleUsage' => $columns->contains(
                    static fn (array $column): bool => (new Collection($column['serverStoreRules']))
                        ->contains(static fn (string $rule): bool => str_contains($rule, 'Rule::')),
                ),
                'hasPassword' => $allColumns->contains(
                    static fn (array $column): bool => $column['name'] === 'password',
                ),
                'hasCreatedByAdminUserId' => $allColumns->contains(
                    static fn (array $column): bool => $column['name'] === 'created_by_admin_user_id',
                ),
                'hasUpdatedByAdminUserId' => $allColumns->contains(
                    static fn (array $column): bool => $column['name'] === 'updated_by_admin_user_id',
                ),

                // validation in store/update
                'columns' => $columns,
                'translatable' => $allColumns
                    ->filter(static fn (array $column): bool => $column['majorType'] === 'json')
                    ->pluck('name'),
                'relations' => $this->relations,
            ],
        )->render();
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating request'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
        ];
    }

    #[Override]
    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return $rootNamespace . '\Http\Requests\Admin\\' . $this->modelWithNamespaceFromDefault;
    }
}
