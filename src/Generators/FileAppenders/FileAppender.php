<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\FileAppenders;

use Brackets\AdminGenerator\Dtos\MediaCollection;
use Brackets\AdminGenerator\Generators\Traits\Columns;
use Brackets\AdminGenerator\Generators\Traits\Helpers;
use Brackets\AdminGenerator\Generators\Traits\Names;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Override;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class FileAppender extends Command
{
    use Helpers;
    use Columns;
    use Names;

    /** @var array<string, string> */
    protected array $relations = [];

    /** @var Collection<string, MediaCollection> */
    protected Collection $mediaCollections;

    public function __construct(protected readonly Filesystem $files)
    {
        parent::__construct();

        $this->mediaCollections = new Collection();
    }

    /**
     * Build the content.
     */
    abstract protected function buildContent(): string;

    /** @return array<array<string|int>> */
    #[Override]
    protected function getArguments(): array
    {
        return [
            ['table_name', InputArgument::REQUIRED, 'Name of the existing table'],
        ];
    }

    /**
     * Append content to file only if if the content is not present in the file
     *
     * @param string $defaultContent content that will be used to populated with newly created file
     * (in case it does not already exist)
     */
    protected function appendIfNotAlreadyAppended(
        string $path,
        string $content,
        string $defaultContent = '<?php' . PHP_EOL . PHP_EOL,
        ?string $checkForAppendedContent = null,
    ): bool {
        if (!$this->files->exists($path)) {
            $this->makeDirectory($path);
            $this->files->put($path, $defaultContent . $content);

            return true;
        }
        if ($checkForAppendedContent !== null && $this->alreadyAppended($path, $checkForAppendedContent)) {
            return false;
        }
        if ($this->alreadyAppended($path, $content)) {
            return false;
        }
        $this->files->append($path, $content);

        return true;
    }

    /**
     * Append content to file only if if the content is not present in the file
     *
     * @param string $defaultContent content that will be used to populated with newly created file
     * (in case it does not already exists)
     */
    protected function replaceIfNotPresent(
        string $path,
        string $search,
        string $replace,
        string $defaultContent = '<?php' . PHP_EOL . PHP_EOL,
        ?string $checkForAppendedContent = null,
    ): bool {
        if (!$this->files->exists($path)) {
            $this->makeDirectory($path);
            $this->files->put($path, $defaultContent);
        }
        if ($checkForAppendedContent !== null && $this->alreadyAppended($path, $checkForAppendedContent)) {
            return false;
        }
        if ($this->alreadyAppended($path, $replace)) {
            return false;
        }
        $this->files->put($path, str_replace($search, $replace, $this->files->get($path)));

        return true;
    }

    /**
     * Replace an existing block identified by root key, or insert before marker if not present.
     *
     * @param string $newBlock Content from buildClass() — starts without indent, ends with marker comment
     * @param string $markerText The marker comment text without leading whitespace
     */
    protected function replaceOrInsertBlock(
        string $path,
        string $rootKey,
        string $newBlock,
        string $markerText,
        string $defaultContent,
    ): bool {
        if (!$this->files->exists($path)) {
            $this->makeDirectory($path);
            $this->files->put($path, $defaultContent);
        }

        $content = $this->files->get($path);
        $keyPattern = "    '" . $rootKey . "' => [";

        if (str_contains($content, $keyPattern)) {
            $startPos = strpos($content, $keyPattern);
            $endPos = $this->findBlockEnd($content, $startPos);
            if ($endPos === false) {
                return false;
            }

            // Strip the marker (and its leading indent) from newBlock — it already exists in the file
            $markerWithIndent = '    ' . $markerText;
            $blockWithoutMarker = str_contains($newBlock, $markerWithIndent)
                ? substr($newBlock, 0, strpos($newBlock, $markerWithIndent))
                : $newBlock;

            $before = substr($content, 0, $startPos);
            $after = substr($content, $endPos);
            $this->files->put($path, $before . '    ' . $blockWithoutMarker . $after);
        } else {
            // Replace marker text (without leading spaces) — the existing indent
            // before the marker becomes the indent for the first line of newBlock
            $this->files->put(
                $path,
                str_replace($markerText, $newBlock, $content),
            );
        }

        return true;
    }

    protected function replaceOrInsertRouteBlock(
        string $path,
        string $resource,
        string $newBlock,
        string $insertMarker,
        string $defaultContent,
    ): bool {
        if (!$this->files->exists($path)) {
            $this->makeDirectory($path);
            $this->files->put($path, $defaultContent);
        }

        $content = $this->files->get($path);
        $startMarker = '/* Auto-generated ' . $resource . ' routes */';
        $endMarker = '/* End of ' . $resource . ' routes */';

        if (str_contains($content, $startMarker)) {
            $startPos = strpos($content, $startMarker);
            $endPos = strpos($content, $endMarker);
            if ($endPos === false) {
                return false;
            }
            $endPos += strlen($endMarker);

            // Skip trailing newlines
            while ($endPos < strlen($content) && $content[$endPos] === "\n") {
                $endPos++;
            }

            $before = substr($content, 0, $startPos);
            $after = substr($content, $endPos);
            $this->files->put($path, $before . $newBlock . $after);
        } elseif (str_contains($content, $insertMarker)) {
            $this->files->put(
                $path,
                str_replace(
                    $insertMarker,
                    $newBlock . '        ' . $insertMarker,
                    $content,
                ),
            );
        } else {
            $this->files->append($path, PHP_EOL . $newBlock);
        }

        return true;
    }

    protected function insertUseStatement(string $path, string $useStatement, string $useMarker): void
    {
        $content = $this->files->get($path);
        if (str_contains($content, $useStatement)) {
            return;
        }

        $this->files->put(
            $path,
            str_replace($useMarker, $useStatement . PHP_EOL . PHP_EOL . $useMarker, $content),
        );
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initCommonNames(
            $this->argument('table_name'),
            $this->option('model-name'),
            $this->option('controller-name'),
            $this->option('model-with-full-namespace'),
        );

        return parent::execute($input, $output);
    }

    private function findBlockEnd(string $content, int $startPos): int|false
    {
        $closingBracketPos = $this->findMatchingClosingBracket($content, $startPos);
        if ($closingBracketPos === false) {
            return false;
        }

        return $this->skipTrailingChars($content, $closingBracketPos + 1);
    }

    private function findMatchingClosingBracket(string $content, int $startPos): int|false
    {
        $depth = 0;
        $length = strlen($content);
        $inString = false;
        $escape = false;

        for ($i = $startPos; $i < $length; $i++) {
            $char = $content[$i];

            if ($escape) {
                $escape = false;

                continue;
            }

            if ($char === '\\') {
                $escape = true;

                continue;
            }

            if ($char === "'") {
                $inString = !$inString;

                continue;
            }

            if ($inString) {
                continue;
            }

            if ($char === '[') {
                $depth++;
            }

            if ($char === ']') {
                $depth--;

                if ($depth === 0) {
                    return $i;
                }
            }
        }

        return false;
    }

    private function skipTrailingChars(string $content, int $pos): int
    {
        $length = strlen($content);

        // Skip comma after closing bracket
        if ($pos < $length && $content[$pos] === ',') {
            $pos++;
        }

        // Skip up to two newlines (line break + blank line)
        for ($n = 0; $n < 2; $n++) {
            if ($pos < $length && $content[$pos] === "\n") {
                $pos++;
            }
        }

        return $pos;
    }
}
