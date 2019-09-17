<?php

declare(strict_types=1);

namespace Ray\ObjectVisualGrapher;

use Ray\Di\Di\Named;
use Ray\Di\ProviderInterface;

class PdoProvider implements ProviderInterface
{
    /**
     * @Named("dsn=dsn,id=id,pass=pass")
     */
    public function __construct(string $dsn, string $id, string $pass)
    {
    }

    public function get()
    {
    }
}