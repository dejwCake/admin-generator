<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Columns\Rules;

final readonly class UniqueRule implements ServerStoreRule
{
    public function __construct(
        private string $tableName,
        private string $columnName,
        private bool $locale = false,
        private bool $deletedAt = false,
    ) {
    }

    public function __toString(): string
    {
        $column = '\'' . $this->columnName . '\'';
        if ($this->locale) {
            $column = '\'' . $this->columnName . '->\'.$locale';
        }

        if ($this->deletedAt) {
            return 'Rule::unique(\'' . $this->tableName . '\', ' . $column . ')' . PHP_EOL .
                '                    ->whereNull(\'deleted_at\')';
        }

        return 'Rule::unique(\'' . $this->tableName . '\', ' . $column . ')';
    }
}
