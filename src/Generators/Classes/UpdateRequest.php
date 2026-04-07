<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Classes;

use Override;
use Symfony\Component\Console\Input\InputOption;

final class UpdateRequest extends ClassGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:request:update';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate an Update request class';

    /**
     * Path for view
     */
    protected string $view = 'classes.update-request';

    public function handle(): void
    {
        $force = $this->option('force');
        $template = $this->option('template');
        $belongsToMany = $this->option('belongs-to-many');

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        if ($template !== null) {
            $this->view = sprintf('classes.templates.%s.update-request', $template);
        }

        $this->relations = $this->relationBuilder->build($this->tableName, $belongsToMany);

        if ($this->generateClass($force)) {
            $this->info(sprintf('Generating %s finished', $this->classFullName));
        }
    }

    /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
    #[Override]
    public function generateClassNameFromTable(string $tableName): string
    {
        return sprintf('Update%s', $this->modelBaseName);
    }

    #[Override]
    protected function buildClass(): string
    {
        $columns = $this->columnCollectionBuilder->build($this->tableName, $this->modelVariableName);
        $visibleColumns = $columns->getVisible();

        return $this->viewFactory->make(sprintf('brackets/admin-generator::%s', $this->view), [
            //globals
            'classBaseName' => $this->classBaseName,
            'classNamespace' => $this->classNamespace,
            'modelBaseName' => $this->modelBaseName,
            'modelFullName' => $this->modelFullName,
            'modelVariableName' => $this->modelVariableName,
            'modelDotNotation' => $this->modelDotNotation,
            'relations' => $this->relations,
            //has
            'hasRuleUsage' => $visibleColumns->hasUpdateRuleUsage(),
            'hasPasswordUsage' => $visibleColumns->hasUpdatePasswordUsage(),
            'hasPassword' => $columns->hasByName('password'),
            'hasCreatedByAdminUser' => $columns->hasByName('created_by_admin_user_id'),
            'hasUpdatedByAdminUser' => $columns->hasByName('updated_by_admin_user_id'),
            'hasPublishedAt' => $columns->hasByName('published_at'),
            //columns
            // validation in store/update
            'visibleColumns' => $visibleColumns,
            'translatableColumns' => $columns->getTranslatable(),
        ])->render();
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['model-with-full-namespace', 'fnm', InputOption::VALUE_OPTIONAL, 'Specify model with full namespace'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating request'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
        ];
    }

    #[Override]
    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return sprintf('%s\Http\Requests\Admin\%s', $rootNamespace, $this->modelWithNamespaceFromDefault);
    }
}
