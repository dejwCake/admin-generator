<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Columns\Rules;

final readonly class UniqueRule implements ServerStoreRule, ServerUpdateRule
{
    public function __construct(
        private string $tableName,
        private string $columnName,
        private ?string $modelVariableName,
        private bool $locale = false,
        private bool $deletedAt = false,
        private bool $ignore = false,
    ) {
    }

    public function __toString(): string
    {
        $column = '\'' . $this->columnName . '\'';
        if ($this->locale) {
            $column = '\'' . $this->columnName . '->\'.$locale';
        }

        $ignore = '';
        if ($this->ignore) {
            //phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
            $ignore = PHP_EOL . '                    ->ignore($this->' . $this->modelVariableName . '->getKey(), $this->' . $this->modelVariableName . '->getKeyName())';
        }

        $deletedAt = '';
        if ($this->deletedAt) {
            $deletedAt = PHP_EOL . '                    ->whereNull(\'deleted_at\')';
        }

        return 'Rule::unique(\'' . $this->tableName . '\', ' . $column . ')'
            . $ignore
            . $deletedAt;
    }
}
