<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator;

use Illuminate\Support\Str;

final class Naming
{
    public static function modelName(string $tableName): string
    {
        return Str::studly(Str::singular($tableName));
    }

    public static function variableName(string $tableName): string
    {
        return Str::lcfirst(self::modelName($tableName));
    }
}
