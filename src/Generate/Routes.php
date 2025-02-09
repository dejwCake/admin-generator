<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate;

use Symfony\Component\Console\Input\InputOption;

class Routes extends FileAppender
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
    protected string $view = 'routes';

    /**
     * Routes have also export route
     */
    protected bool $export = false;

    /**
     * Routes have also bulk options route
     */
    protected bool $withoutBulk = false;

    public function handle(): void
    {
        if ($this->option('with-export')) {
            $this->export = true;
        }

        if ($this->option('without-bulk')) {
            $this->withoutBulk = true;
        }

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        if (!empty($template = $this->option('template'))) {
            $this->view = 'templates.' . $template . '.routes';
        }

        if ($this->appendIfNotAlreadyAppended(base_path('routes/web.php'), PHP_EOL . PHP_EOL . $this->buildClass())) {
            $this->info('Appending routes finished');
        }
    }

    protected function buildClass(): string
    {
        return view('brackets/admin-generator::' . $this->view, [
            'controllerPartiallyFullName' => $this->controllerWithNamespaceFromDefault,
            'modelVariableName' => $this->modelVariableName,
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'resource' => $this->resource,
            'export' => $this->export,
            'withoutBulk' => $this->withoutBulk,
        ])->render();
    }

    /** @return array<array<string|int>> */
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a controller for the given model'],
            ['controller-name', 'c', InputOption::VALUE_OPTIONAL, 'Specify custom controller name'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['with-export', 'e', InputOption::VALUE_NONE, 'Generate an option to Export as Excel'],
            ['without-bulk', 'wb', InputOption::VALUE_NONE, 'Generate without bulk options'],
        ];
    }
}
