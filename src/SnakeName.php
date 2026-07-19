<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

use function str_replace;

final class SnakeName
{
    public function __invoke(string $class): string
    {
        return str_replace('\\', '_', $class);
    }
}
