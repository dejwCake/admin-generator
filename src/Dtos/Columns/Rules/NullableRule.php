<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Columns\Rules;

final readonly class NullableRule implements ServerStoreRule
{
    public function __toString(): string
    {
        return '\'nullable\'';
    }
}
