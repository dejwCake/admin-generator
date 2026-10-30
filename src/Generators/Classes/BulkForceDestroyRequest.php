<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Classes;

final class BulkForceDestroyRequest extends TrashRequest
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $name = 'admin:generate:request:bulk-force-destroy';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Generate a Bulk Force Destroy request class';

    /**
     * Path for view
     */
    protected string $view = 'classes.bulk-force-destroy-request';

    /**
     * Prefix of the generated class name
     */
    protected string $classPrefix = 'BulkForceDestroy';
}
