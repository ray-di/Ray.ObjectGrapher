<?php

declare(strict_types=1);

namespace Ray\ObjectVisualGrapher;

use PHPUnit\Framework\TestCase;

class ObjectVisualGrapherTest extends TestCase
{
    /**
     * @var ObjectVisualGrapher
     */
    protected $objectVisualGrapher;

    protected function setUp() : void
    {
        $this->objectVisualGrapher = new ObjectVisualGrapher;
    }

    public function testIsInstanceOfObjectVisualGrapher() : void
    {
        $actual = $this->objectVisualGrapher;
        $this->assertInstanceOf(ObjectVisualGrapher::class, $actual);
    }
}
