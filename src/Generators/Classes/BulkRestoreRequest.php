<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Classes;

final class BulkRestoreRequest extends TrashRequest
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:request:bulk-restore';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate a Bulk Restore request class';

    /**
     * Path for view
     */
    protected string $view = 'classes.bulk-restore-request';

    /**
     * Prefix of the generated class name
     */
    protected string $classPrefix = 'BulkRestore';
}
