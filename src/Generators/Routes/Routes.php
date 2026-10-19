<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Routes;

use Brackets\AdminGenerator\Dtos\GeneratedActions;
use Brackets\AdminGenerator\Enums\GeneratedAction;
use Brackets\AdminGenerator\Generators\Generator;
use Brackets\AdminGenerator\Generators\Traits\ResolvesGeneratedActions;
use Override;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class Routes extends Generator
{
    use ResolvesGeneratedActions;

    private const string UMBRELLA_FILE = <<<'PHP'
        <?php

        declare(strict_types=1);

        use Illuminate\Support\Facades\Route;

        Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])
            ->prefix('admin')
            ->name('admin/')
            ->group(static function (): void {
                foreach (glob(__DIR__ . '/admin/*.php') as $file) {
                    require $file;
                }
            });

        PHP;

    protected GeneratedActions $generatedActions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:routes';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Write a per-resource admin route file and create the umbrella file if missing';

    /**
     * Path for view (default — overridden when --template is supplied).
     */
    protected string $view = 'routes.routes';

    /**
     * Resource name
     */
    protected string $resource = '';

    public function handle(): void
    {
        $template = $this->option('template');

        if ($template !== null) {
            $this->view = sprintf('routes.templates.%s.routes', $template);
        }

        $this->generatedActions = $this->resolveGeneratedActions();

        $this->resource = $this->option('resource') ?? $this->resource;

        $this->ensureUmbrella();
        $this->writeResourceFile();
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
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a controller for the given model'],
            ['controller-name', 'c', InputOption::VALUE_OPTIONAL, 'Specify custom controller name'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ...$this->getGeneratedActionOptions(),
            ['resource', 'r', InputOption::VALUE_OPTIONAL, 'Specify custom resource name for route identification'],
        ];
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initCommonNames(
            $this->argument('table_name'),
            $this->option('model-name'),
            $this->option('controller-name'),
            $this->option('model-with-full-namespace'),
        );

        return parent::execute($input, $output);
    }

    private function ensureUmbrella(): void
    {
        $umbrellaPath = $this->laravel->basePath('routes/admin.php');

        if ($this->files->exists($umbrellaPath)) {
            return;
        }

        $this->makeDirectory($umbrellaPath);
        $this->files->put($umbrellaPath, self::UMBRELLA_FILE);
        $this->info('Admin route umbrella created at routes/admin.php');
    }

    private function writeResourceFile(): void
    {
        $resourceFile = $this->laravel->basePath(sprintf('routes/admin/%s.php', $this->resource));

        $this->makeDirectory($resourceFile);
        $this->files->put($resourceFile, $this->buildContent());
        $this->info(sprintf('Admin route file written: routes/admin/%s.php', $this->resource));
    }

    private function buildContent(): string
    {
        return $this->viewFactory->make(sprintf('brackets/admin-generator::%s', $this->view), [
            'controllerFullName' => $this->controllerFullName,
            'controllerBaseName' => basename(str_replace('\\', '/', $this->controllerFullName)),
            'modelVariableName' => $this->modelVariableName,
            'resource' => $this->resource,
            //has
            'hasCreate' => $this->generatedActions->has(GeneratedAction::Create),
            'hasShow' => $this->generatedActions->has(GeneratedAction::Show),
            'hasEdit' => $this->generatedActions->has(GeneratedAction::Edit),
            'hasDelete' => $this->generatedActions->has(GeneratedAction::Delete),
            'hasBulkDelete' => $this->generatedActions->has(GeneratedAction::BulkDelete),
            'hasExport' => $this->generatedActions->has(GeneratedAction::Export),
        ])->render();
    }
}
