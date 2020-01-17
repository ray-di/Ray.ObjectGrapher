<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

final class SnakeName
{
    public function __invoke(string $class) : string
    {
        return str_replace('\\', '_', $class);
    }
}
