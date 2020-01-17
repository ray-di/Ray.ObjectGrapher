<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

final class ClassId
{
    public function __invoke(string $class) : string
    {
        return 'class_' . (new SnakeName)($class);
    }
}
