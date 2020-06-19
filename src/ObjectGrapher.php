<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

use Ray\Di\AbstractModule;
use Ray\Di\Argument;
use Ray\Di\Bind;
use Ray\Di\Container;
use Ray\Di\Dependency;
use Ray\Di\DependencyInterface;
use Ray\Di\DependencyProvider;
use Ray\Di\Instance;

final class ObjectGrapher
{
    /**
     * @var Prop
     */
    private $prop;

    /**
     * @var Container
     */
    private $container;
    /**
     * @var Graph
     */
    private $graph;

    /**
     * @var DependencyId
     */
    private $dependencyId;

    /**
     * @var ClassId
     */
    private $classId;

    /**
     * @var SnakeName
     */
    private $snakeName;

    public function __construct()
    {
        $this->prop = new Prop;
        $this->graph = new Graph;
        $this->dependencyId = new DependencyId;
        $this->classId = new ClassId;
        $this->snakeName = new SnakeName;
    }

    public function __invoke(AbstractModule $module) : string
    {
        $this->init();
        $this->container = $module->getContainer();
        foreach ($this->container->getContainer() as $dependencyIndex => $dependency) {
            [$type, $name] = explode('-', $dependencyIndex);
            $this->setGraph($type, $name, $dependency);
        }

        return $this->toString();
    }

    private function init() : void
    {
        Arrow::$history = ToClass::$index = ClassNode::$ids = [];
    }

    private function setGraph(string $type, string $name, DependencyInterface $dependency) : void
    {
        $isTargetBinding = ! interface_exists($type);
        $dependencyId = ($this->dependencyId)($type, $name);
        if (! $isTargetBinding) {
            $this->graph->addNode(new InterfaceNode($dependencyId, $type, $name));
        }
        if ($dependency instanceof Instance) {
            $this->graph->addNode(new InstanceNode($dependencyId, $type, $name, $dependency));
        }
        if ($dependency instanceof Dependency) {
            $this->dependencyNode($dependencyId, new MyDependency($dependency), $isTargetBinding);
        }
        if ($dependency instanceof DependencyProvider) {
            $this->providerNode($dependencyId, $dependency);
        }
    }

    private function dependencyNode(string $interfaceId, MyDependency $dependency, bool $isTargetBinding) : void
    {
        if (! $isTargetBinding) {
            $this->graph->addArrow(new ToClass($interfaceId, $dependency->classId));
        }
        $setters = $this->lineDependency($dependency);
        $this->graph->addNode(new ClassNode($dependency->classId, $dependency->class, $setters));
    }

    private function providerNode(string $interfaceId, DependencyProvider $dependency) : void
    {
        $dependency = new MyDependency(($this->prop)($dependency, 'dependency'));
        $setters = $this->lineDependency($dependency);
        $this->graph->addNode(new ProviderNode($dependency->classId, $dependency->class, $setters));
        if ($interfaceId) {
            $this->graph->addArrow(new ToProvider($interfaceId, $dependency->classId));
        }
        $this->graph->addNode(new ClassNode($dependency->classId, $dependency->class, $setters));
    }

    /**
     * @return array<string> setter symbol
     */
    private function lineDependency(MyDependency $dependency) : array
    {
        if (! $dependency->arguments) {
            return [];
        }
        // constructor injection
        $arguments = ($this->prop)($dependency->arguments, 'arguments');
        $port = sprintf('p_%s_construct', ($this->snakeName)($dependency->class));
        $setters = ($this->prop)($dependency->arguments, 'arguments') === [] ? [] : ['construct' => $port];
        $this->drawInjectionGraph($arguments, $dependency->classId, $port);
        // setter injection
        $setterMethods = ($this->prop)($dependency->setterMethods, 'setterMethods');
        foreach ($setterMethods as $setterMethod) {
            $setterMethodArguments = ($this->prop)($setterMethod, 'arguments');
            $arguments = ($this->prop)($setterMethodArguments, 'arguments');
            $setterMethodName = ($this->prop)($setterMethod, 'method');
            $setterMethodPort = sprintf('p_%s_%s', ($this->snakeName)($dependency->class), $setterMethodName);
            $this->drawInjectionGraph($arguments, $dependency->classId, $setterMethodPort);
            $setters += [$setterMethodName => $setterMethodPort];
        }

        return $setters;
    }

    private function setterArrow(string $classPort, string $dependencyIndex) : void
    {
        [$type, $name] = \explode('-', $dependencyIndex);
        $dependencyId = ($this->dependencyId)($type, $name);
        $this->graph->addArrow(new Arrow($classPort, $dependencyId, $type));
        if (class_exists($type)) {
            $this->addClassNode($dependencyIndex);

            return;
        }
        $this->graph->addNode(new InterfaceNode($dependencyId, $type, $name));
    }

    private function addClassNode(string $dependencyIndex) : void
    {
        [$type, $name] = \explode('-', $dependencyIndex);
        assert(class_exists($type));
        $isAbstract = (new \ReflectionClass($type))->isAbstract();
        if ($isAbstract) {
            $this->graph->addNode(new InterfaceNode(($this->classId)($type), $type, $name));

            return;
        }
        $container = $this->container->getContainer();
        if (! isset($container[$dependencyIndex])) {
            $this->bindOnTheFly($dependencyIndex, $type, $name);
        }
    }

    /**
     * @param array<Argument> $arguments Arguments
     * @param string          $classId   class ID
     * @param string          $port      port ID
     */
    private function drawInjectionGraph(array $arguments, string $classId, string $port) : void
    {
        foreach ($arguments as $argument) {
            assert($argument instanceof Argument);
            $dependencyIndex = ($this->prop)($argument, 'index');
            $classPort = sprintf('%s:%s:e', $classId, $port);
            $this->setterArrow($classPort, $dependencyIndex);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function bindOnTheFly(string $dependencyIndex, string $type, string $name) : void
    {
        $this->container->add((new Bind($this->container, $type))->annotatedWith($name)->to($type));
        $dependency = $this->container->getContainer()[$dependencyIndex];
        assert($dependency instanceof Dependency);
        $setters = $this->lineDependency(new MyDependency($dependency));
        $this->graph->addNode(new ClassNode(($this->classId)($type), $type, $setters));
    }

    private function toString() : string
    {
        return <<<EOT
digraph injector {
graph [rankdir=TB];
{$this->graph}
}
EOT;
    }
}
