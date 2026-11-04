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
        private array $scopeColumns = [],
    ) {
    }

    public function __toString(): string
    {
        $column = sprintf("'%s'", $this->columnName);
        if ($this->locale) {
            $column = sprintf("'%s'->'.\$locale", $this->columnName);
        }

        $scope = '';
        foreach ($this->scopeColumns as $scopeColumn) {
            $scope .= sprintf(
                PHP_EOL . "                    ->where('%s', \$this->input('%s'%s))",
                $scopeColumn,
                $scopeColumn,
                $this->ignore ? sprintf(', $this->%s->%s', $this->modelVariableName, $scopeColumn) : '',
            );
        }

        $ignore = '';
        if ($this->ignore) {
            //phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
            $ignore = sprintf(
                PHP_EOL . '                    ->ignore($this->%s->getKey(), $this->%s->getKeyName())',
                $this->modelVariableName,
                $this->modelVariableName,
            );
        }

        $deletedAt = '';
        if ($this->deletedAt) {
            $deletedAt = PHP_EOL . '                    ->whereNull(\'deleted_at\')';
        }

        return sprintf("Rule::unique('%s', %s)", $this->tableName, $column)
            . $scope
            . $ignore
            . $deletedAt;
    }
}
