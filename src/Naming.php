<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator;

use Illuminate\Support\Str;

final class Naming
{
    public static function modelName(string $tableName): string
    {
        return Str::pascal(Str::singular($tableName));
    }

    /**
     * The controller holding the restore and force-delete actions of a soft-deleting model,
     * named after its main controller: CategoriesController → CategoriesTrashController.
     */
    public static function trashControllerName(string $controllerName): string
    {
        return Str::endsWith($controllerName, 'Controller')
            ? Str::replaceLast('Controller', 'TrashController', $controllerName)
            : sprintf('%sTrash', $controllerName);
    }

    public static function variableName(string $tableName): string
    {
        return Str::lcfirst(self::modelName($tableName));
    }
}
