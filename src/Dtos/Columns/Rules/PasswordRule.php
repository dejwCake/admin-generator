<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Columns\Rules;

final readonly class PasswordRule implements ServerStoreRule, ServerUpdateRule
{
    public function __construct(private int $length)
    {
    }

    public function __toString(): string
    {
        return sprintf('Password::min(%d)', $this->length) . PHP_EOL .
            '                    ->letters()' . PHP_EOL .
            '                    ->mixedCase()' . PHP_EOL .
            '                    ->numbers()' . PHP_EOL .
            '                    ->symbols()' . PHP_EOL .
            '                    ->uncompromised()';
    }
}
