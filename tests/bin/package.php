<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use BEAR\Package\PackageModule;
use Ray\ObjectGrapher\ObjectGrapher;

$dot = (new ObjectGrapher)(new PackageModule);
$file = __DIR__ . '/package.dot';
file_put_contents($file, $dot);
