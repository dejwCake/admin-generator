<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Traits;

trait Helpers
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingAnyTypeHint
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint
     */
    public function option($key = null)
    {
        return $key === null || $this->hasOption($key) ? parent::option($key) : null;
    }

    /**
     * Build the directory for the class if necessary.
     */
    protected function makeDirectory(string $path): string
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    /**
     * Determine if the file already exists.
     */
    protected function alreadyExists(string $path): bool|string
    {
        return $this->files->exists($path);
    }

    /**
     * Determine if the content is already present in the file
     */
    protected function alreadyAppended(string $path, string $content): bool
    {
        return str_contains($this->files->get($path), $content);
    }
}
