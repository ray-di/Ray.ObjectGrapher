<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

use function array_keys;
use BEAR\Package\PackageModule;
use BEAR\Resource\Module\ResourceModule;
use PHPUnit\Framework\TestCase;
use Ray\Aop\Matcher;

use Ray\Aop\Pointcut;

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
        $this->assertStringContainsString('dependency_Ray_ObjectGrapher_LoggerInterface_ -> class_Ray_ObjectGrapher_DatabaseLogger', $dot);
        $this->assertStringContainsString('class_Ray_ObjectGrapher_DatabaseLogger:p_Ray_ObjectGrapher_DatabaseLogger_construct:e -> dependency_Ray_ObjectGrapher_PdoInterface_', $dot);
        $this->assertStringContainsString('dependency_Ray_ObjectGrapher_PdoInterface_ -> class_Ray_ObjectGrapher_PdoProvider [style=dashed, arrowtail=none, arrowhead=onormalonormal]', $dot);
    }

    public function test__invokeBearResource() : void
    {
        $dot = ($this->objectGrapher)(new ResourceModule('a'));
        $this->assertStringContainsString('dependency__BEAR_Resource_Annotation_AppName [style', $dot);
        $this->assertStringContainsString('dependency_BEAR_Resource_ResourceInterface_ -> class_BEAR_Resource_Resource', $dot);
        $this->assertStringContainsString('dependency_BEAR_Resource_SchemeCollectionInterface_ -> class_BEAR_Resource_Module_SchemeCollectionProvider [style=dashed, arrowtail=none, arrowhead=onormalonormal]', $dot);
    }

    public function test__invokeBearPackage() : void
    {
        $dot = ($this->objectGrapher)(new PackageModule);
        $this->assertStringContainsString('_BEAR_Sunday_Provide_Transfer_ConditionalResponseInterface_ -> class_BEAR_Sunday_Provide_Transfer_ConditionalResponse [style=dashed, arrowtail=none, arrowhead=onormal]', $dot);
    }

    public function testConcreteDependencyAnalysisDoesNotMutateModule() : void
    {
        $module = new ConcreteModule;
        $container = $module->getContainer();
        $container->addPointcut(new Pointcut((new Matcher)->any(), (new Matcher)->any(), [DatabaseLogger::class]));
        $keys = array_keys($container->getContainer());
        $events = $container->log->getEvents();
        $sources = $container->log->getSources();
        $pointcuts = $container->getPointcuts();

        $first = ($this->objectGrapher)($module);
        $second = ($this->objectGrapher)($module);

        $this->assertSame($first, $second);
        $this->assertStringContainsString(
            'class_Ray_ObjectGrapher_ConcreteRoot:p_Ray_ObjectGrapher_ConcreteRoot_construct:e -> class_Ray_ObjectGrapher_ConcreteDependency',
            $first
        );
        $this->assertSame($keys, array_keys($container->getContainer()));
        $this->assertSame($events, $container->log->getEvents());
        $this->assertSame($sources, $container->log->getSources());
        $this->assertSame($pointcuts, $container->getPointcuts());
    }
}
