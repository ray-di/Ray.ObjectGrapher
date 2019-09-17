<?php

declare(strict_types=1);

namespace Ray\ObjectVisualGrapher;

use Ray\Di\AbstractModule;

final class FakeModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(LoggerInterface::class)->to(DatabaseLogger::class);
        $this->bind(PdoInterface::class)->toProvider(PdoProvider::class);
        $this->bind()->annotatedWith('dsn')->toInstance('');
        $this->bind()->annotatedWith('id')->toInstance('');
        $this->bind()->annotatedWith('pass')->toInstance('');
    }
}