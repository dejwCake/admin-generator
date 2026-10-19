<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Classes;

use Override;
use Symfony\Component\Console\Input\InputOption;

final class Permissions extends ClassGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:permissions';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate permissions migration';

    /**
     * Path for view
     */
    protected string $view = 'classes.permissions';

    /**
     * Permissions has also bulk options
     */
    protected bool $withImpersonalLogin = false;

    public function handle(): void
    {
        $force = $this->option('force');

        if ($this->option('with-impersonal-login')) {
            $this->withImpersonalLogin = true;
        }

        if ($this->generateClass($force)) {
            $this->info(sprintf('Generating permissions for %s finished', $this->modelBaseName));
        }
    }

    /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
    #[Override]
    public function generateClassNameFromTable(string $tableName): string
    {
        return sprintf('FillPermissionsFor%s', $this->modelBaseName);
    }

    #[Override]
    protected function generateClass(bool $force = false): bool
    {
        $fileName = sprintf('fill_permissions_for_%s.php', $this->modelRouteAndViewName);
        $path = $this->laravel->databasePath(sprintf('migrations/%s_%s', date('Y_m_d_His', time()), $fileName));

        $oldPath = $this->alreadyExists($fileName);
        if ($oldPath) {
            $path = $oldPath;
            if ($force) {
                $this->warn(sprintf('File %s already exists! File will be deleted.', $path));
                $this->files->delete($path);
            } else {
                $this->error(sprintf('File %s already exists!', $path));

                return false;
            }
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->buildClass());

        return true;
    }

    /**
     * Determine if the file already exists.
     */
    protected function alreadyExists(string $path): bool|string
    {
        if (!$this->files->exists($this->laravel->databasePath('migrations'))) {
            return false;
        }

        foreach ($this->files->files($this->laravel->databasePath('migrations')) as $file) {
            if (str_contains($file->getFilename(), $path)) {
                return $file->getPathname();
            }
        }

        return false;
    }

    #[Override]
    protected function buildClass(): string
    {
        return $this->viewFactory->make(sprintf('brackets/admin-generator::%s', $this->view), [
            //globals
            'modelDotNotation' => $this->modelDotNotation,
            //has
            'hasImpersonalLogin' => $this->withImpersonalLogin,
        ])->render();
    }

    /** @return array<array<string|int|null>> */
    #[Override]
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            [
                'without-bulk',
                'wb',
                InputOption::VALUE_NONE,
                'Deprecated and ignored: the permission set is always complete',
            ],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating request'],
            [
                'with-impersonal-login',
                null,
                InputOption::VALUE_NONE,
                'Include the impersonal-login permission in the generated migration',
            ],
        ];
    }
}
