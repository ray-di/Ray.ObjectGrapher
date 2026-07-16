<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

use Ray\Di\AbstractModule;

final class ConcreteModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(ConcreteRoot::class);
    }
}
