<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Columns\Rules;

final readonly class TimeRule implements ServerStoreRule, ServerUpdateRule
{
    public function __toString(): string
    {
        return 'Rule::date()->format(\'H:i:s\')';
    }
}
