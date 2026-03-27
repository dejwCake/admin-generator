<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Traits;

trait FileManipulations
{
    private function strReplaceInFile(
        string $filePath,
        string $find,
        string $replaceWith,
        ?string $ifRegexNotExists = null,
    ): bool|int {
        $filesystem = $this->files;
        $content = $filesystem->get($filePath);
        if ($ifRegexNotExists !== null && preg_match($ifRegexNotExists, $content)) {
            return false;
        }

        return $filesystem->put($filePath, str_replace($find, $replaceWith, $content));
    }
}
