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
    private $prop;

    public function __construct()
    {
        $this->prop = new Prop;
    }

    public function __invoke(AbstractModule $module) : string
    {
        $container = $module->getContainer()->getContainer();
        $graph = $this->getGraph($container);

        return $this->toString($graph->nodes, $graph->arrows);
    }

    public function getGraph(array $container) : Graph
    {
        $graph = new Graph;
        foreach ($container as $dependencyIndex => $dependency) {
            [$type, $name] = explode('-', $dependencyIndex);
            $this->setGraph($graph, $type, $name, $dependency);
        }

        return $graph;
    }

    public function providerNode(string $interfaceId, $dependency, Graph $graph) : void
    {
        $dependency = ($this->prop)($dependency, 'dependency');
        $newInstance = ($this->prop)($dependency, 'newInstance');
        $class = ($this->prop)($newInstance, 'class');
        $classId = $this->getClassId($class);
        $arguments = ($this->prop)($newInstance, 'arguments');
        $setterMethods = ($this->prop)($newInstance, 'setterMethods');
        $setters = $this->lineDependency($graph, $class, $arguments, $setterMethods, $classId);
        $graph->nodes[] = new ProviderNode($classId, $class, $setters);
        if ($interfaceId) {
            $graph->arrows[] = new ToProvider($interfaceId, $classId);
        }
        $graph->nodes[] = new ClassNode($classId, $class, $setters);
    }

    public function getSnakeName($class) : string
    {
        return str_replace('\\', '_', $class);
    }

    public function lineDependency(Graph $graph, $class, ?Arguments $arguments, SetterMethods $setterMethods, string $classId) : array
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
        $graph->arrows[] = new Arrow($classPort, $dependencyId, $type);
        $graph->nodes[] = new InterfaceNode($dependencyId, $type, $name);
    }

    private function drawInjectionGraph(Graph $graph, array $arguments, string $classId, string $port) : void
    {
        foreach ($arguments as $argument) {
            /** @var Argument $arugment */
            $dependencyIndex = ($this->prop)($argument, 'index');
            $classPort = sprintf('%s:%s:e', $classId, $port);
            $this->setterArrow($graph, $classPort, $dependencyIndex);
        }
    }

    private function setGraph(Graph $graph, $type, $name, DependencyInterface $dependency) : void
    {
        $isTargetBinding = ! interface_exists($type);
        $dependencyId = $this->getDependencyId($type, $name);
        if (! $isTargetBinding) {
            $graph->nodes[] = new InterfaceNode($dependencyId, $type, $name);
        }
        if ($dependency instanceof Dependency) {
            $this->dependencyNode($dependencyId, $dependency, $graph, $isTargetBinding);
        }
        if ($dependency instanceof DependencyProvider) {
            $this->providerNode($dependencyId, $dependency, $graph);
        }
        if ($dependency instanceof Instance) {
            $graph->nodes[] = new InstanceNode($dependencyId, $type, $name);
        }
    }

    private function getDependencyId(string $interace, string $name) : string
    {
        if (class_exists($interace)) {
            return $this->getClassId($interace);
        }

        return sprintf('t_%s_%s', $this->getSnakeName($interace), $this->getSnakeName($name));
    }

    private function getClassId(string $class)
    {
        return 'c_' . $this->getSnakeName($class);
    }

    private function toString(array $nodes, array $arrows) : string
    {
        $string = '';
        foreach ($nodes as $node) {
            $string .= (string) $node . PHP_EOL;
        }
        $string .= PHP_EOL;
        foreach ($arrows as $arrow) {
            $string .= (string) $arrow . PHP_EOL;
        }

        return <<<EOT
digraph injector {
graph [rankdir=TB];
${string}
}
EOT;
    }

    private function instanceNode(Graph $graph, string $type, string $name) : void
    {
        $dependencyId = $this->getDependencyId($type, $name);
        $graph->nodes[] = new InstanceNode($dependencyId, $type, $name);
    }

    private function dependencyNode(string $interfaceId, DependencyInterface $dependency, Graph $graph, bool $isTargetBinding) : void
    {
        $newInstance = ($this->prop)($dependency, 'newInstance');
        $class = ($this->prop)($newInstance, 'class');
        $classId = $this->getClassId($class);
        $nodes[] = new ClassNode($classId, $class, ['construct' => null]);
        if (! $isTargetBinding) {
            $graph->arrows[] = new ToClass($interfaceId, $classId);
        }
        // constructor
        $arguments = ($this->prop)($newInstance, 'arguments');
        $setterMethods = ($this->prop)($newInstance, 'setterMethods');
        $setters = $this->lineDependency($graph, $class, $arguments, $setterMethods, $classId);
        $graph->nodes[] = new ClassNode($classId, $class, $setters);
    }
}
