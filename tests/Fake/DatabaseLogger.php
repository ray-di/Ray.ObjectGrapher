<?php

declare(strict_types=1);

namespace Ray\ObjectVisualGrapher;

class DatabaseLogger implements LoggerInterface
{
    public function __construct(PdoInterface $pdo)
    {
    }
}