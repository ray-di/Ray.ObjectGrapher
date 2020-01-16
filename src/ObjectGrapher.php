<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

use Ray\Di\AbstractModule;
use Ray\Di\Argument;
use Ray\Di\Arguments;
use Ray\Di\Dependency;
use Ray\Di\DependencyInterface;
use Ray\Di\DependencyProvider;
use Ray\Di\Instance;
use Ray\Di\SetterMethods;

final class ObjectGrapher
{
    /**
     * @var Prop
     */
    private $prop;

    public function __construct()
    {
        $this->prop = new Prop;
    }

    public function __invoke(AbstractModule $module) : string
    {
        $this->init();
        $container = $module->getContainer()->getContainer();
        $graph = $this->getGraph($container);

        return $this->toString($graph);
    }

    /**
     * @param array<DependencyInterface> $container
     */
    public function getGraph(array $container) : Graph
    {
        $graph = new Graph;
        foreach ($container as $dependencyIndex => $dependency) {
            [$type, $name] = explode('-', $dependencyIndex);
            $this->setGraph($graph, $type, $name, $dependency);
        }

        return $graph;
    }

    public function providerNode(string $interfaceId, DependencyInterface $dependency, Graph $graph) : void
    {
        $dependency = ($this->prop)($dependency, 'dependency');
        $newInstance = ($this->prop)($dependency, 'newInstance');
        $class = ($this->prop)($newInstance, 'class');
        $classId = $this->getClassId($class);
        $arguments = ($this->prop)($newInstance, 'arguments');
        $setterMethods = ($this->prop)($newInstance, 'setterMethods');
        $setters = $this->lineDependency($graph, $class, $arguments, $setterMethods, $classId);
        $graph->addNode(new ProviderNode($classId, $class, $setters));
        if ($interfaceId) {
            $graph->addArrow(new ToProvider($interfaceId, $classId));
        }
        $graph->addNode(new ClassNode($classId, $class, $setters));
    }

    public function getSnakeName(string $class) : string
    {
        return str_replace('\\', '_', $class);
    }

    /**
     * @return array<string> setter symbol
     */
    public function lineDependency(Graph $graph, string $class, ?Arguments $arguments, SetterMethods $setterMethods, string $classId) : array
    {
        if (! $arguments) {
            return [];
        }
        // constructor injection
        $arguments = ($this->prop)($arguments, 'arguments');
        $port = sprintf('p_%s_construct', $this->getSnakeName($class));
        $setters = ['construct' => $port];
        $this->drawInjectionGraph($graph, $arguments, $classId, $port);
        // setter injection
        $setterMethods = ($this->prop)($setterMethods, 'setterMethods');

        foreach ($setterMethods as $setterMethod) {
            $setterMethodArguments = ($this->prop)($setterMethod, 'arguments');
            $arguments = ($this->prop)($setterMethodArguments, 'arguments');
            $setterMethodName = ($this->prop)($setterMethod, 'method');
            $setterMethodPort = sprintf('p_%s_%s', $this->getSnakeName($class), $setterMethodName);
            $this->drawInjectionGraph($graph, $arguments, $classId, $setterMethodPort);
            $setters += [$setterMethodName => $setterMethodPort];
        }

        return $setters;
    }

    public function setterArrow(Graph $graph, string $classPort, string $dependencyIndex) : void
    {
        [$type, $name] = \explode('-', $dependencyIndex);
        $dependencyId = $this->getDependencyId($type, $name);
        $graph->addArrow(new Arrow($classPort, $dependencyId, $type));
        $graph->addNode(new InterfaceNode($dependencyId, $type, $name));
    }

    /**
     * @param Graph           $graph     Graph
     * @param array<Argument> $arguments Arguments
     * @param string          $classId   class ID
     * @param string          $port      port ID
     */
    private function drawInjectionGraph(Graph $graph, array $arguments, string $classId, string $port) : void
    {
        foreach ($arguments as $argument) {
            assert($argument instanceof Argument);
            $dependencyIndex = ($this->prop)($argument, 'index');
            $classPort = sprintf('%s:%s:e', $classId, $port);
            $this->setterArrow($graph, $classPort, $dependencyIndex);
        }
    }

    private function setGraph(Graph $graph, string $type, string $name, DependencyInterface $dependency) : void
    {
        $isTargetBinding = ! interface_exists($type);
        $dependencyId = $this->getDependencyId($type, $name);
        if (! $isTargetBinding) {
            $graph->addNode(new InterfaceNode($dependencyId, $type, $name));
        }
        if ($dependency instanceof Dependency) {
            $this->dependencyNode($dependencyId, $dependency, $graph, $isTargetBinding);
        }
        if ($dependency instanceof DependencyProvider) {
            $this->providerNode($dependencyId, $dependency, $graph);
        }
        if ($dependency instanceof Instance) {
            $graph->addNode(new InstanceNode($dependencyId, $type, $name));
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

    private function toString(Graph $graph) : string
    {
        return <<<EOT
digraph injector {
graph [rankdir=TB];
{$graph}
}
EOT;
    }

    private function dependencyNode(string $interfaceId, DependencyInterface $dependency, Graph $graph, bool $isTargetBinding) : void
    {
        $newInstance = ($this->prop)($dependency, 'newInstance');
        $class = ($this->prop)($newInstance, 'class');
        $classId = $this->getClassId($class);
        if (! $isTargetBinding) {
            $graph->addArrow(new ToClass($interfaceId, $classId));
        }
        // constructor
        $arguments = ($this->prop)($newInstance, 'arguments');
        $setterMethods = ($this->prop)($newInstance, 'setterMethods');
        $setters = $this->lineDependency($graph, $class, $arguments, $setterMethods, $classId);
        $graph->addNode(new ClassNode($classId, $class, $setters));
    }

    private function init() : void
    {
        Arrow::$history = [];
        ToClass::$index = [];
    }
}
