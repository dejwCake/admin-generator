<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generate;

use Brackets\AdminGenerator\Generate\Traits\Columns;
use Brackets\AdminGenerator\Generate\Traits\Helpers;
use Brackets\AdminGenerator\Generate\Traits\Names;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
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

    public function __construct(protected readonly Filesystem $files)
    {
        parent::__construct();
    }

    /** @return array<array<string|int>> */
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
    ): bool {
        if (!$this->files->exists($path)) {
            $this->makeDirectory($path);
            $this->files->put($path, $defaultContent . $content);

            return true;
        }
        if (!$this->alreadyAppended($path, $content)) {
            $this->files->append($path, $content);

            return true;
        }

        return false;
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
    ): bool {
        if (!$this->files->exists($path)) {
            $this->makeDirectory($path);
            $this->files->put($path, $defaultContent);
        }

        if (!$this->alreadyAppended($path, $replace)) {
            $this->files->put($path, str_replace($search, $replace, $this->files->get($path)));

            return true;
        }

        return false;
    }

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
}
