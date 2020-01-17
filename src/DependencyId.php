<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

final class DependencyId
{
    public function __invoke(string $interace, string $name) : string
    {
        if (class_exists($interace)) {
            return (new ClassId)($interace);
        }
        $snakeName = new SnakeName;

        return sprintf('dependency_%s_%s', ($snakeName)($interace), ($snakeName)($name));
    }
}
