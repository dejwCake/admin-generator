<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Tests\Feature\Generators;

use Brackets\AdminGenerator\Enums\GeneratedAction;

use function array_intersect;
use function array_keys;
use function array_map;

final class GeneratedActionCases
{
    /**
     * @return iterable<string, array{options: array<string, string|bool>, actions: list<GeneratedAction>}>
     */
    public static function get(): iterable
    {
        $create = GeneratedAction::Create;
        $show = GeneratedAction::Show;
        $edit = GeneratedAction::Edit;
        $delete = GeneratedAction::Delete;
        $export = GeneratedAction::Export;
        $bulkDelete = GeneratedAction::BulkDelete;

        yield 'only create' => ['options' => ['--only' => 'create'], 'actions' => [$create]];
        yield 'only show' => ['options' => ['--only' => 'show'], 'actions' => [$show]];
        yield 'only edit' => ['options' => ['--only' => 'edit'], 'actions' => [$edit]];
        yield 'only delete' => ['options' => ['--only' => 'delete'], 'actions' => [$delete]];
        yield 'only bulk-delete' => ['options' => ['--only' => 'bulk-delete'], 'actions' => [$bulkDelete]];
        yield 'only export' => ['options' => ['--only' => 'export'], 'actions' => [$export]];

        yield 'except create' => [
            'options' => ['--except' => 'create'],
            'actions' => [$show, $edit, $delete, $export, $bulkDelete],
        ];

        yield 'except show' => [
            'options' => ['--except' => 'show'],
            'actions' => [$create, $edit, $delete, $export, $bulkDelete],
        ];

        yield 'except edit' => [
            'options' => ['--except' => 'edit'],
            'actions' => [$create, $show, $delete, $export, $bulkDelete],
        ];

        yield 'except delete' => [
            'options' => ['--except' => 'delete'],
            'actions' => [$create, $show, $edit, $export, $bulkDelete],
        ];

        yield 'except bulk-delete' => [
            'options' => ['--except' => 'bulk-delete'],
            'actions' => [$create, $show, $edit, $delete, $export],
        ];

        yield 'only create with-export' => [
            'options' => ['--only' => 'create', '--with-export' => true],
            'actions' => [$create, $export],
        ];

        yield 'except export with-export' => [
            'options' => ['--except' => 'export', '--with-export' => true],
            'actions' => [$create, $show, $edit, $delete, $bulkDelete, $export],
        ];

        yield 'except create without-bulk' => [
            'options' => ['--except' => 'create', '--without-bulk' => true],
            'actions' => [$show, $edit, $delete, $export],
        ];

        yield 'with-export without-bulk' => [
            'options' => ['--with-export' => true, '--without-bulk' => true],
            'actions' => [$create, $show, $edit, $delete, $export],
        ];
    }

    /**
     * @param list<GeneratedAction> $actions
     * @param array<string, list<GeneratedAction>> $filesByAction a file is expected when any of its
     *     actions is selected; an empty list means it is always expected
     * @return array{expectedFiles: list<string>, missingFiles: list<string>}
     */
    public static function splitFiles(array $actions, array $filesByAction): array
    {
        $expectedFiles = [];
        $missingFiles = [];
        $selected = array_map(static fn (GeneratedAction $action): string => $action->value, $actions);

        foreach (array_keys($filesByAction) as $path) {
            $required = array_map(
                static fn (GeneratedAction $action): string => $action->value,
                $filesByAction[$path],
            );

            if ($required === [] || array_intersect($required, $selected) !== []) {
                $expectedFiles[] = $path;
            } else {
                $missingFiles[] = $path;
            }
        }

        return ['expectedFiles' => $expectedFiles, 'missingFiles' => $missingFiles];
    }
}
