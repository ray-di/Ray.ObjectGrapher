<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

final class Graph
{
    /**
     * Object Nodes
     *
     * @var array
     */
    public $nodes;

    /**
     * Object Dependency Graph
     *
     * @var array
     */
    public $arrows;
}
