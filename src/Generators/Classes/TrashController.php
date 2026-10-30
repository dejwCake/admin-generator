<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Classes;

use Brackets\AdminGenerator\Dtos\GeneratedActions;
use Brackets\AdminGenerator\Enums\GeneratedAction;
use Brackets\AdminGenerator\Generators\Traits\ResolvesGeneratedActions;
use Brackets\AdminGenerator\Naming;
use Illuminate\Support\Str;
use Override;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class TrashController extends ClassGenerator
{
    use ResolvesGeneratedActions;

    protected GeneratedActions $generatedActions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:trash-controller';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate a controller restoring and permanently deleting soft-deleted rows';

    /**
     * Path for view
     */
    protected string $view = 'classes.trash-controller';

    public function handle(): void
    {
        $force = $this->option('force');

        $this->generatedActions = $this->resolveGeneratedActions();

        if (
            !$this->generatedActions->has(GeneratedAction::Delete)
            && !$this->generatedActions->has(GeneratedAction::BulkDelete)
        ) {
            $this->warn('Skipping the trash controller: neither delete nor bulk-delete is generated.');

            return;
        }

        if ($this->generateClass($force)) {
            $this->info(sprintf('Generating %s finished', $this->classFullName));
        }
    }

    #[Override]
    public function generateClassNameFromTable(string $tableName): string
    {
        return Naming::trashControllerName(sprintf('%sController', Str::studly($tableName)));
    }

    #[Override]
    protected function buildClass(): string
    {
        return $this->viewFactory->make(sprintf('brackets/admin-generator::%s', $this->view), [
            //globals
            'controllerBaseName' => $this->classBaseName,
            'controllerNamespace' => $this->classNamespace,
            'modelBaseName' => $this->modelBaseName,
            'modelFullName' => $this->modelFullName,
            'modelVariableName' => $this->modelVariableName,
            'modelWithNamespaceFromDefault' => $this->modelWithNamespaceFromDefault,
            //has
            'hasDelete' => $this->generatedActions->has(GeneratedAction::Delete),
            'hasBulkDelete' => $this->generatedActions->has(GeneratedAction::BulkDelete),
        ])->render();
    }

    /** @return array<int, array<string|int|null>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['model-with-full-namespace', 'fnm', InputOption::VALUE_OPTIONAL, 'Specify model with full namespace'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating controller'],
            ...$this->getGeneratedActionOptions(),
        ];
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getArguments(): array
    {
        return array_merge(
            parent::getArguments(),
            [
                ['class_name', InputArgument::OPTIONAL, 'Name of the generated class'],
            ],
        );
    }

    #[Override]
    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return sprintf('%s\Http\Controllers\Admin', $rootNamespace);
    }
}
