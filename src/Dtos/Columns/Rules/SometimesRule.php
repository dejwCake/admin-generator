<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Columns\Rules;

final readonly class SometimesRule implements ServerUpdateRule
{
    public function __toString(): string
    {
        return '\'sometimes\'';
    }
}
