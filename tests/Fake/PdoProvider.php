<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

use Ray\Di\Di\Inject;
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

    /**
     * @Inject
     */
    public function setFoo(FooInterface $foo)
    {
    }

    public function get()
    {
    }
}
