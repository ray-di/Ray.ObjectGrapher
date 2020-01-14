<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

use BEAR\Package\PackageModule;
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
        $this->assertStringContainsString('t_Ray_ObjectGrapher_LoggerInterface_ -> c_Ray_ObjectGrapher_DatabaseLogger', $dot);
        $this->assertStringContainsString('c_Ray_ObjectGrapher_DatabaseLogger:p_Ray_ObjectGrapher_DatabaseLogger_construct:e -> t_Ray_ObjectGrapher_PdoInterface_', $dot);
        $this->assertStringContainsString('c_Ray_ObjectGrapher_DatabaseLogger:p_Ray_ObjectGrapher_DatabaseLogger_setFoo:e -> t_Ray_ObjectGrapher_FooInterface_', $dot);
        $this->assertStringContainsString('t_Ray_ObjectGrapher_PdoInterface_ -> c_Ray_ObjectGrapher_PdoProvider [style=dashed, arrowtail=none, arrowhead=onormalonormal]', $dot);
    }

    public function test__invokeBearResource() : void
    {
        $dot = ($this->objectGrapher)(new ResourceModule('a'));
        $file = __DIR__ . '/res.dot';
        file_put_contents($file, $dot);
        $dot = file_get_contents($file);
        $this->assertStringContainsString('t__BEAR_Resource_Annotation_AppName [style', $dot);
        $this->assertStringContainsString('t_BEAR_Resource_ResourceInterface_ -> c_BEAR_Resource_Resource', $dot);
        $this->assertStringContainsString('t_Symfony_Contracts_HttpClient_HttpClientInterface_ -> c_BEAR_Resource_Module_HttpClientProvider [style=dashed, arrowtail=none, arrowhead=onormalonormal]', $dot);
    }

    public function test__invokeBearPackage() : void
    {
        $dot = ($this->objectGrapher)(new PackageModule);
        $file = __DIR__ . '/package.dot';
        file_put_contents($file, $dot);
        $dot = file_get_contents($file);
        $this->assertStringContainsString('t_BEAR_Sunday_Provide_Transfer_ConditionalResponseInterface_ -> c_BEAR_Sunday_Provide_Transfer_ConditionalResponse [style=dashed, arrowtail=none, arrowhead=onormal]', $dot);
    }
}
