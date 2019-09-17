<?php

namespace Ray\ObjectVisualGrapher;

final class ToClass
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
{$this->interface} -> {$this->class} [style=dashed, arrowtail=none, arrowhead=onormal]
EOT;
    }
}