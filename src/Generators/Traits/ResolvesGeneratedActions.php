<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Traits;

use Brackets\AdminGenerator\Dtos\GeneratedActions;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * @phpstan-require-extends Command
 */
trait ResolvesGeneratedActions
{
    private function resolveGeneratedActions(): GeneratedActions
    {
        $only = $this->option('only');
        $except = $this->option('except');

        return GeneratedActions::fromOptions(
            is_string($only) ? $only : null,
            is_string($except) ? $except : null,
            (bool) $this->option('with-export'),
            (bool) $this->option('without-bulk'),
        );
    }

    /**
     * @return array<array<string|int|null>>
     */
    private function getGeneratedActionOptions(): array
    {
        return [
            [
                'only',
                null,
                InputOption::VALUE_OPTIONAL,
                'Generate only these actions: create, show, edit, delete, export, bulk-delete (plus index)',
            ],
            [
                'except',
                null,
                InputOption::VALUE_OPTIONAL,
                'Generate every action except these (index is always generated)',
            ],
            [
                'with-export',
                'e',
                InputOption::VALUE_NONE,
                'Deprecated, use --only/--except: generate an option to Export as Excel',
            ],
            [
                'without-bulk',
                'wb',
                InputOption::VALUE_NONE,
                'Deprecated, use --only/--except: generate without bulk options',
            ],
        ];
    }
}
