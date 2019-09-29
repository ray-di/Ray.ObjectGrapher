<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

final class ToProvider implements ArrowInterface
{
    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    private $interface;

    public function __construct(string $interface, string $class)
    {
        $this->class = $class;
        $this->interface = $interface;
    }

    public function __toString()
    {
        return <<<EOT
{$this->interface} -> {$this->class} [style=dashed, arrowtail=none, arrowhead=onormalonormal]
EOT;
    }
}
