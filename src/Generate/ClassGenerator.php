<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate;

use Brackets\AdminGenerator\Generate\Traits\Columns;
use Brackets\AdminGenerator\Generate\Traits\Helpers;
use Brackets\AdminGenerator\Generate\Traits\Names;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ClassGenerator extends Command
{
    use Helpers;
    use Columns;
    use Names;

    protected string $classBaseName;
    protected string $classFullName;
    protected string $classNamespace;

    /** @var array<string> */
    protected array $relations = [];

    /**
     * Create a new controller creator command instance.
     */
    public function __construct(protected readonly Filesystem $files)
    {
        parent::__construct();
    }

    /**
     * Generate default class name (for case if not passed as argument) from table name
     */
    abstract public function generateClassNameFromTable(string $tableName): string;

    /**
     * Build the class with the given name.
     */
    abstract protected function buildClass(): string;

    public function getPathFromClassName(string $name): string
    {
        $path = str_replace('\\', '/', $name) . ".php";

        return preg_replace('|^App/|', 'app/', $path);
    }

    /**
     * Get the root namespace for the class.
     */
    public function rootNamespace(): string
    {
        return $this->laravel->getNamespace();
    }

    /**
     * Parse the class name and format according to the root namespace.
     */
    public function qualifyClass(string $name): string
    {
        $name = str_replace('/', '\\', $name);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name,
        );
    }

    /** @return array<array<string|int>> */
    protected function getArguments(): array
    {
        return [
            ['table_name', InputArgument::REQUIRED, 'Name of the existing table'],
            ['class_name', InputArgument::OPTIONAL, 'Name of the generated class'],
        ];
    }

    /**
     * Get the full namespace for a given class, without the class name.
     */
    protected function getNamespace(string $name): string
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return $rootNamespace;
    }

    protected function generateClass(bool $force = false): bool
    {
        $path = base_path($this->getPathFromClassName($this->classFullName));

        if ($this->alreadyExists($path)) {
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
     * Execute the console command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this instanceof Model) {
            $this->initCommonNames(
                $this->argument('table_name'),
                $this->argument('class_name'),
                null,
                $this->option('model-with-full-namespace'),
            );
        } else {
            $this->initCommonNames(
                $this->argument('table_name'),
                $this->option('model-name'),
                null,
                $this->option('model-with-full-namespace'),
            );
        }

        $this->initClassNames($this->argument('class_name'));

        return parent::execute($input, $output);
    }

    protected function initClassNames(?string $className = null): void
    {
        if ($className === null) {
            $className = $this->generateClassNameFromTable($this->tableName);
        }

        $this->classFullName = $this->qualifyClass($className);
        $this->classBaseName = class_basename($this->classFullName);
        $this->classNamespace = Str::replaceLast("\\" . $this->classBaseName, '', $this->classFullName);
    }
}
