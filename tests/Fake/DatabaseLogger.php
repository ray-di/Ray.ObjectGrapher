<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

class DatabaseLogger implements LoggerInterface
{
    public function __construct(PdoInterface $pdo)
    {
    }
}
