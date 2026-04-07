<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\FileAppenders;

use Override;
use Symfony\Component\Console\Input\InputOption;

final class Routes extends FileAppender
{
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
    protected $description = 'Append admin routes into a web routes file';

    /**
     * Path for view
     */
    protected string $view = 'file-appenders.routes';

    /**
     * Routes have also export route
     */
    protected bool $export = false;

    /**
     * Routes have also bulk options route
     */
    protected bool $withoutBulk = false;

    /**
     * Resource name
     */
    protected string $resource = '';

    public function handle(): void
    {
        $template = $this->option('template');
        $withExport = $this->option('with-export');
        $withoutBulk = $this->option('without-bulk');

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        if ($template !== null) {
            $this->view = sprintf('file-appenders.templates.%s.routes', $template);
        }

        if ($withExport) {
            $this->export = true;
        }

        if ($withoutBulk) {
            $this->withoutBulk = true;
        }

        $routesPath = base_path('routes/admin.php');
        $insertMarker = "// Do not delete me :) I'm used for auto-generation of admin routes";
        $useMarker = PHP_EOL . '/* Auto-generated admin routes uses */';

        $defaultContent = '<?php' . PHP_EOL . PHP_EOL
            . 'declare(strict_types=1);' . PHP_EOL . PHP_EOL
            . 'use Illuminate\Support\Facades\Route;' . PHP_EOL
            . $useMarker . PHP_EOL . PHP_EOL
            . "Route::middleware(['auth:' . config('admin-auth.defaults.guard'), 'admin'])" . PHP_EOL
            . "    ->prefix('admin')" . PHP_EOL
            . "    ->name('admin/')" . PHP_EOL
            . '    ->group(static function (): void {' . PHP_EOL
            . sprintf('        %s', $insertMarker) . PHP_EOL
            . '    });' . PHP_EOL;

        $this->resource = $this->option('resource') ?? $this->resource;

        if (
            $this->replaceOrInsertRouteBlock(
                $routesPath,
                $this->resource,
                $this->buildContent() . PHP_EOL,
                $insertMarker,
                $defaultContent,
            )
        ) {
            $this->insertUseStatement($routesPath, sprintf('use %s;', $this->controllerFullName), $useMarker);
            $this->info('Appending routes finished');
        }
    }

    /** @return array<array<string|int>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a controller for the given model'],
            ['controller-name', 'c', InputOption::VALUE_OPTIONAL, 'Specify custom controller name'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['with-export', 'e', InputOption::VALUE_NONE, 'Generate an option to Export as Excel'],
            ['without-bulk', 'wb', InputOption::VALUE_NONE, 'Generate without bulk options'],
            ['resource', 'r', InputOption::VALUE_OPTIONAL, 'Specify custom resource name for route identification'],
        ];
    }

    protected function buildContent(): string
    {
        return $this->viewFactory->make(sprintf('brackets/admin-generator::%s', $this->view), [
            //globals
            'controllerBaseName' => class_basename($this->controllerFullName),
            'modelVariableName' => $this->modelVariableName,
            'resource' => $this->resource,
            //has
            'hasExport' => $this->export,
            'hasBulk' => !$this->withoutBulk,
        ])->render();
    }
}
