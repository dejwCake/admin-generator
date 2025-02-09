<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate\Traits;

use Illuminate\Filesystem\Filesystem;

trait FileManipulations
{
    private function strReplaceInFile(
        string $filePath,
        string $find,
        string $replaceWith,
        ?string $ifRegexNotExists = null,
    ): bool|int {
        $filesystem = app(Filesystem::class);
        $content = $filesystem->get($filePath);
        if ($ifRegexNotExists !== null && preg_match($ifRegexNotExists, $content)) {
            return false;
        }

        return $filesystem->put($filePath, str_replace($find, $replaceWith, $content));
    }
}
