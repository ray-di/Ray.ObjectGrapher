<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

use BEAR\Resource\Module\ResourceModule;
use PHPUnit\Framework\TestCase;

class ObjectVisualGrapherTest extends TestCase
{
    /**
     * @var ObjectGrapher
     */
    protected $objectGrapher;

    protected function setUp() : void
    {
        $this->objectGrapher = new ObjectGrapher;
    }

    public function testIsInstanceOfObjectGrapher() : void
    {
        $actual = $this->objectGrapher;
        $this->assertInstanceOf(ObjectGrapher::class, $actual);
    }

    public function test__invoke() : void
    {
        $dot = ($this->objectGrapher)(new FakeModule());
        $file = __DIR__ . '/fake.dot';
        file_put_contents($file, $dot);
        $dot = file_get_contents($file);
    }

    public function test__invokeBearResource() : void
    {
        $dot = ($this->objectGrapher)(new ResourceModule('a'));
        $file = __DIR__ . '/res.dot';
        file_put_contents($file, $dot);
        $dot = file_get_contents($file);
        $this->assertContains('t__BEAR_Resource_Annotation_AppName [style', $dot);
        $this->assertContains('t_BEAR_Resource_ResourceInterface_ -> c_BEAR_Resource_Resource', $dot);
    }
}
