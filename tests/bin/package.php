<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use BEAR\AppMeta\Meta;
use BEAR\Package\AppMetaModule;
use BEAR\Package\PackageModule;
use Ray\ObjectGrapher\ObjectGrapher;

$dot = (new ObjectGrapher)(new AppMetaModule(new Meta('Ray\ObjectGrapher'), new PackageModule));
$file = __DIR__ . '/package.dot';
file_put_contents($file, $dot);
