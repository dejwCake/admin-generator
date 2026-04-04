<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Generators\Traits;

use Brackets\AdminGenerator\Dtos\Columns\Column;

trait Columns
{
    protected function getRelatedLabelColumn(string $tableName): string
    {
        $columns = $this->columnCollectionBuilder->build($tableName);
        $preferredLabels = ['title', 'name', 'first_name', 'email'];

        foreach ($preferredLabels as $label) {
            if ($columns->has($label)) {
                return $label;
            }
        }

        $firstString = $columns->first(
            static fn (Column $column): bool => $column->majorType === 'string',
        );

        return $firstString?->name ?? 'id';
    }
}
