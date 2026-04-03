<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Columns\Rules;

final readonly class StringRule implements ServerStoreRule, ServerUpdateRule
{
    public function __toString(): string
    {
        return '\'string\'';
    }
}
