<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

use Ray\Di\Di\Inject;

class DatabaseLogger implements LoggerInterface
{
    public function __construct(PdoInterface $pdo)
    {
    }

    /**
     * @Inject
     */
    public function setFoo(FooInterface $foo)
    {
    }
}
