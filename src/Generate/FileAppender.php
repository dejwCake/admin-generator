<?php namespace Brackets\AdminGenerator\Generate;

use Brackets\AdminGenerator\Generate\Traits\Helpers;
use Brackets\AdminGenerator\Generate\Traits\Names;
use Brackets\AdminGenerator\Generate\Traits\Columns;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class FileAppender extends Command {

    use Helpers, Columns, Names;

    /**
     * @var array<string>
     */
    protected array $relations = [];

    /**
     * Create a new controller creator command instance.
     */
    public function __construct(protected readonly Filesystem $files)
    {
        parent::__construct();
    }

    /** @return array<array<string|int>> */
    protected function getArguments():array {
        return [
            ['table_name', InputArgument::REQUIRED, 'Name of the existing table'],
        ];
    }

    /**
     * Append content to file only if if the content is not present in the file
     *
     * @param string $defaultContent content that will be used to populated with newly created file
     * (in case it does not already exists)
     */
    protected function appendIfNotAlreadyAppended(string $path, string $content, string $defaultContent = "<?php".PHP_EOL.PHP_EOL): bool
    {
        if (!$this->files->exists($path)) {
            $this->makeDirectory($path);
            $this->files->put($path, $defaultContent.$content);
        } else if (!$this->alreadyAppended($path, $content)) {
            $this->files->append($path, $content);
        } else {
            return false;
        }

        return true;
    }

    /**
     * Append content to file only if if the content is not present in the file
     *
     * @param string $defaultContent content that will be used to populated with newly created file
     * (in case it does not already exists)
     */
    protected function replaceIfNotPresent(string $path, string $search, string $replace, string $defaultContent = "<?php".PHP_EOL.PHP_EOL): bool
    {
        if (!$this->files->exists($path)) {
            $this->makeDirectory($path);
            $this->files->put($path, $defaultContent);
        }

        if (!$this->alreadyAppended($path, $replace)) {
            $this->files->put($path, str_replace($search, $replace, $this->files->get($path)));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Execute the console command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initCommonNames($this->argument('table_name'), $this->option('model-name'), $this->option('controller-name'), $this->option('model-with-full-namespace'));

        return parent::execute($input, $output);
    }
}
