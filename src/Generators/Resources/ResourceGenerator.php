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
    abstract protected function build(): string;

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
            $this->error('File ' . $path . ' already exists!');

            return;
        }

        if ($this->alreadyExists($path) && $force) {
            $this->warn('File ' . $path . ' already exists! File will be deleted.');
            $this->files->delete($path);
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->build());
        $this->info('Generating ' . $path . ' finished');
    }

    protected function registerVueComponent(string $componentName, string $jsRelativePath, string $fileName): void
    {
        $adminJsPath = resource_path('js/admin/admin.js');

        if (!$this->files->exists($adminJsPath)) {
            $this->warn('File ' . $adminJsPath . ' does not exist, skipping component registration.');

            return;
        }

        $content = $this->files->get($adminJsPath);

        $importMarker = '//-- Do not delete me :) I\'m used for auto-generation js import--';
        $componentMarker = '//-- Do not delete me :) I\'m used for auto-generation component registration--';

        $importLine = "import {$componentName} from './{$jsRelativePath}/{$fileName}';";
        $componentLine = "app.component('{$componentName}', {$componentName});";

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
