<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Resources;

use Brackets\AdminGenerator\Generators\Generator;
use Override;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ResourceGenerator extends Generator
{
    abstract protected function buildView(): string;

    /** @return array<array<string|int>> */
    #[Override]
    protected function getArguments(): array
    {
        return [
            ['table_name', InputArgument::REQUIRED, 'Name of the existing table'],
            // FIXME add OPTIONAL file_name argument
        ];
    }

    protected function generate(string $path, bool $force): void
    {
        if ($this->alreadyExists($path) && !$force) {
            $this->error(sprintf('File %s already exists!', $path));

            return;
        }

        if ($this->alreadyExists($path) && $force) {
            $this->warn(sprintf('File %s already exists! File will be deleted.', $path));
            $this->files->delete($path);
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->buildView());
        $this->info(sprintf('Generating %s finished', $path));
    }

    protected function registerVueComponent(string $componentName, string $jsRelativePath, string $fileName): void
    {
        $adminJsPath = $this->laravel->resourcePath('js/admin/admin.js');

        if (!$this->files->exists($adminJsPath)) {
            $this->warn(sprintf('File %s does not exist, skipping component registration.', $adminJsPath));

            return;
        }

        $content = $this->files->get($adminJsPath);

        $importMarker = '//-- Do not delete me :) I\'m used for auto-generation js import--';
        $componentMarker = '//-- Do not delete me :) I\'m used for auto-generation component registration--';

        $importLine = sprintf("import %s from './%s/%s';", $componentName, $jsRelativePath, $fileName);
        $componentLine = sprintf("app.component('%s', %s);", $componentName, $componentName);

        if (!str_contains($content, $importLine)) {
            $content = str_replace($importMarker, $importLine . PHP_EOL . $importMarker, $content);
        }

        if (!str_contains($content, $componentLine)) {
            $content = str_replace($componentMarker, $componentLine . PHP_EOL . $componentMarker, $content);
        }

        $this->files->put($adminJsPath, $content);
    }

    /**
     * Append content to file only if if the content is not present in the file
     */
    protected function appendIfNotAlreadyAppended(string $path, string $content): bool
    {
        if (!$this->files->exists($path)) {
            $this->makeDirectory($path);
            $this->files->put($path, $content);
        } elseif (!$this->alreadyAppended($path, $content)) {
            $this->files->append($path, $content);
        } else {
            return false;
        }

        return true;
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initCommonNames($this->argument('table_name'), $this->option('model-name'));

        return parent::execute($input, $output);
    }
}
