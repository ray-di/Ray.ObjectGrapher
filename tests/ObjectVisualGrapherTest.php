<?php

declare(strict_types=1);

namespace Ray\ObjectVisualGrapher;

use BEAR\Resource\Module\ResourceModule;
use BEAR\Sunday\Provide\Application\AppModule;
use PHPUnit\Framework\TestCase;

class ObjectVisualGrapherTest extends TestCase
{
    /**
     * @var ObjectVisualGrapher
     */
    protected $objectVisualGrapher;

    protected function setUp() : void
    {
        $this->objectVisualGrapher = new ObjectVisualGrapher(new ResourceModule('a'));
    }

    public function testIsInstanceOfObjectVisualGrapher() : void
    {
        $actual = $this->objectVisualGrapher;
        $this->assertInstanceOf(ObjectVisualGrapher::class, $actual);
    }

    public function test__invoke()
    {
        $dot = ($this->objectVisualGrapher)();
        file_put_contents(dirname(__DIR__) . '/g.dot', $dot);
    }
}
