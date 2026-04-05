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
    /** @return array<array<string|int>> */
    #[Override]
    protected function getArguments(): array
    {
        return [
            ['table_name', InputArgument::REQUIRED, 'Name of the existing table'],
            // FIXME add OPTIONAL file_name argument
        ];
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
