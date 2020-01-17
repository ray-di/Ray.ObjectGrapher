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

    public function __construct()
    {
        $this->prop = new Prop;
        $this->graph = new Graph;
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

    public function providerNode(string $interfaceId, DependencyInterface $dependency) : void
    {
        $dependency = ($this->prop)($dependency, 'dependency');
        $newInstance = ($this->prop)($dependency, 'newInstance');
        $class = ($this->prop)($newInstance, 'class');
        $classId = $this->getClassId($class);
        $setters = $this->lineDependency($dependency);
        $this->graph->addNode(new ProviderNode($classId, $class, $setters));
        if ($interfaceId) {
            $this->graph->addArrow(new ToProvider($interfaceId, $classId));
        }
        $this->graph->addNode(new ClassNode($classId, $class, $setters));
    }

    public function getSnakeName(string $class) : string
    {
        return str_replace('\\', '_', $class);
    }

    /**
     * @return array<string> setter symbol
     */
    public function lineDependency(Dependency $dependency) : array
    {
        $newInstance = ($this->prop)($dependency, 'newInstance');
        $class = ($this->prop)($newInstance, 'class');
        $classId = $this->getClassId($class);
        $arguments = ($this->prop)($newInstance, 'arguments');
        $setterMethods = ($this->prop)($newInstance, 'setterMethods');

        if (! $arguments) {
            return [];
        }
        // constructor injection
        $arguments = ($this->prop)($arguments, 'arguments');
        $port = sprintf('p_%s_construct', $this->getSnakeName($class));
        $setters = ['construct' => $port];
        $this->drawInjectionGraph($arguments, $classId, $port);
        // setter injection
        $setterMethods = ($this->prop)($setterMethods, 'setterMethods');

        foreach ($setterMethods as $setterMethod) {
            $setterMethodArguments = ($this->prop)($setterMethod, 'arguments');
            $arguments = ($this->prop)($setterMethodArguments, 'arguments');
            $setterMethodName = ($this->prop)($setterMethod, 'method');
            $setterMethodPort = sprintf('p_%s_%s', $this->getSnakeName($class), $setterMethodName);
            $this->drawInjectionGraph($arguments, $classId, $setterMethodPort);
            $setters += [$setterMethodName => $setterMethodPort];
        }

        return $setters;
    }

    public function setterArrow(string $classPort, string $dependencyIndex) : void
    {
        [$type, $name] = \explode('-', $dependencyIndex);
        $dependencyId = $this->getDependencyId($type, $name);
        $this->graph->addArrow(new Arrow($classPort, $dependencyId, $type));
        if (class_exists($type)) {
            $this->addClassNode($dependencyIndex, $type, $name);

            return;
        }
        $this->graph->addNode(new InterfaceNode($dependencyId, $type, $name));
    }

    private function addClassNode(string $dependencyIndex, string $type) : void
    {
        $isAbstract = (new \ReflectionClass($type))->isAbstract();
        if ($isAbstract) {
            $this->graph->addNode(new InterfaceNode($this->getClassId($type), $type, ''));

            return;
        }
        $container = $this->container->getContainer();
        if (! isset($container[$dependencyIndex])) {
            // bind on the fly
            $this->container->add((new Bind($this->container, $type))->to($type));
        }
        $dependency = $this->container->getContainer()[$dependencyIndex];
        if ($dependency instanceof Dependency) {
            $setters = $this->lineDependency($dependency);
            $this->graph->addNode(new ClassNode($this->getClassId($type), $type, $setters));
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

    private function setGraph(string $type, string $name, DependencyInterface $dependency) : void
    {
        $isTargetBinding = ! interface_exists($type);
        $dependencyId = $this->getDependencyId($type, $name);
        if (! $isTargetBinding) {
            $this->graph->addNode(new InterfaceNode($dependencyId, $type, $name));
        }
        if ($dependency instanceof Dependency) {
            $this->dependencyNode($dependencyId, $dependency, $isTargetBinding);
        }
        if ($dependency instanceof DependencyProvider) {
            $this->providerNode($dependencyId, $dependency);
        }
        if ($dependency instanceof Instance) {
            $this->graph->addNode(new InstanceNode($dependencyId, $type, $name));
        }
    }

    private function getDependencyId(string $interace, string $name) : string
    {
        if (class_exists($interace)) {
            return $this->getClassId($interace);
        }

        return sprintf('t_%s_%s', $this->getSnakeName($interace), $this->getSnakeName($name));
    }

    private function getClassId(string $class) : string
    {
        return 'c_' . $this->getSnakeName($class);
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

    private function dependencyNode(string $interfaceId, DependencyInterface $dependency, bool $isTargetBinding) : void
    {
        $newInstance = ($this->prop)($dependency, 'newInstance');
        $class = ($this->prop)($newInstance, 'class');
        $classId = $this->getClassId($class);
        if (! $isTargetBinding) {
            $this->graph->addArrow(new ToClass($interfaceId, $classId));
        }
        // constructor
        if ($dependency instanceof Dependency) {
            $setters = $this->lineDependency($dependency);
            $this->graph->addNode(new ClassNode($classId, $class, $setters));
        }
    }

    private function init() : void
    {
        Arrow::$history = [];
        ToClass::$index = [];
    }
}
