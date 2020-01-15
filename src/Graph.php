<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

final class Graph
{
    /**
     * Object Nodes
     *
     * @var array<NodeInterface>
     */
    private $nodes;

    /**
     * Object Dependency Graph
     *
     * @var array<ArrowInterface>
     */
    private $arrows;

    public function __toString()
    {
        $string = '';
        foreach ($this->nodes as $node) {
            $string .= (string) $node . PHP_EOL;
        }
        $string .= PHP_EOL;
        foreach ($this->arrows as $arrow) {
            $string .= (string) $arrow . PHP_EOL;
        }

        return $string;
    }

    public function addNode(NodeInterface $node) : void
    {
        $this->nodes[] = $node;
    }

    public function addArrow(ArrowInterface $arrow) : void
    {
        $this->arrows[] = $arrow;
    }
}
