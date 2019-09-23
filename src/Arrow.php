<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

use Ray\Aop\ReflectionClass;
use Ray\Di\ProviderInterface;

final class Arrow
{
    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    private $interface;

    /**
     * @var string
     */
    private $arrowHead;

    public function __construct(string $fromId, string $ToId, string $toClass)
    {
        $this->class = $ToId;
        $this->interface = $fromId;
        $isProvider = class_exists($toClass) && (new ReflectionClass($toClass))->implementsInterface(ProviderInterface::class);
        $this->arrowHead = $isProvider ? 'arrowhead=onormalonormal' : 'arrowhead=onormal';
    }

    public function __toString()
    {
        return <<<EOT
{$this->interface} -> {$this->class} [style=dashed, arrowtail=none, {$this->arrowHead}]
EOT;
    }
}
