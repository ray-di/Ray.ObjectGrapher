<?php

declare(strict_types=1);

namespace Ray\ObjectVisualGrapher;

use BEAR\Resource\Module\ResourceModule;
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

    public function test__invoke() : void
    {
        $dot = ($this->objectVisualGrapher)();
        $file = dirname(__DIR__) . '/g.dot';
        file_put_contents($file, $dot);
        $dot = file_get_contents($file);
        $this->assertContains('t__BEAR_Resource_Annotation_AppName [style', $dot);
        $this->assertContains('t_BEAR_Resource_ResourceInterface_ -> c_BEAR_Resource_Resource', $dot);
    }
}
