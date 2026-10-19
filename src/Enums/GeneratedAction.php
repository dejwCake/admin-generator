<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Enums;

use function array_column;

enum GeneratedAction: string
{
    case Create = 'create';

    case Show = 'show';

    case Edit = 'edit';

    case Delete = 'delete';

    case Export = 'export';

    case BulkDelete = 'bulk-delete';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
