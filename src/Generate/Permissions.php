<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate;

use Symfony\Component\Console\Input\InputOption;

class Permissions extends ClassGenerator
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
     * Permissions has also bulk options
     */
    protected bool $withoutBulk = false;

    public function handle(): void
    {
        $force = $this->option('force');

        if ($this->option('without-bulk')) {
            $this->withoutBulk = true;
        }

        if ($this->generateClass($force)) {
            $this->info('Generating permissions for ' . $this->modelBaseName . ' finished');
        }
    }

    /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
    public function generateClassNameFromTable(string $tableName): string
    {
        return 'FillPermissionsFor' . $this->modelBaseName;
    }

    protected function generateClass(bool $force = false): bool
    {
        $fileName = 'fill_permissions_for_' . $this->modelRouteAndViewName . '.php';
        $path = database_path('migrations/' . date('Y_m_d_His', time()) . '_' . $fileName);

        $oldPath = $this->alreadyExists($fileName);
        if ($oldPath) {
            $path = $oldPath;
            if ($force) {
                $this->warn('File ' . $path . ' already exists! File will be deleted.');
                $this->files->delete($path);
            } else {
                $this->error('File ' . $path . ' already exists!');

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
        foreach ($this->files->files(database_path('migrations')) as $file) {
            if (str_contains($file->getFilename(), $path)) {
                return $file->getPathname();
            }
        }

        return false;
    }

    protected function buildClass(): string
    {
        return view('brackets/admin-generator::permissions', [
            'modelBaseName' => $this->modelBaseName,
            'modelDotNotation' => $this->modelDotNotation,
            'className' => $this->generateClassNameFromTable($this->tableName),
            'withoutBulk' => $this->withoutBulk,
        ])->render();
    }

    /** @return array<array<string|int>> */
    protected function getOptions(): array
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating request'],
            ['without-bulk', 'wb', InputOption::VALUE_NONE, 'Generate without bulk options'],
        ];
    }
}
