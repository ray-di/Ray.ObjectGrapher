<?php

declare(strict_types=1);

namespace Ray\ObjectVisualGrapher;

use Ray\Di\AbstractModule;
use Ray\Di\Argument;
use Ray\Di\Dependency;
use Ray\Di\DependencyInterface;
use Ray\Di\DependencyProvider;
use Ray\Di\Instance;

class ObjectVisualGrapher
{
    /**
     * @var array|\Ray\Di\Dependency[]
     */
    private $container;

    private $prop;
    
    public function __construct(AbstractModule $module)
    {
        $this->container = $module->getContainer()->getContainer();
        $this->prop = new Prop;
    }

    public function __invoke() : string
    {
        $graph = $this->getGraph();

        return $this->toString($graph->nodes, $graph->arrows);
    }

    public function getGraph() : Graph
    {
        $graph = new Graph;
        foreach ($this->container as $dependencyIndex => $dependency) {
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
        $firstArguments = ($this->prop)($newInstance, 'arguments');
        $setters = [];
        if ($firstArguments) {
            $arguments = ($this->prop)($firstArguments, 'arguments');
            $setters = $this->getSetters($graph, $class, $arguments, $classId);
            $graph->nodes[] = new ProviderNode($classId, $class, $setters);
        }
        if ($interfaceId) {
            $graph->arrows[] = new ToProvider($interfaceId, $classId);
        }
        $graph->nodes[] = new ClassNode($classId, $class, $setters);
    }

    public function getSnakeName($class) : string
    {
        return str_replace('\\', '_', $class);
    }

    public function getSetters(Graph $graph, $class, $arguments, string $classId) : array
    {
        $port = sprintf('p_%s_construct', $this->getSnakeName($class));
        $setters = ['construct' => $port];
        foreach ($arguments as $argument) {
            /** @var Argument $arugment */
            $dependencyIndex = ($this->prop)($argument, 'index');
            $classPort = sprintf('%s:%s:e', $classId, $port);
            $this->setterArrow($graph, $classPort, $dependencyIndex);
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
        $firstArguments = ($this->prop)($newInstance, 'arguments');
        if ($firstArguments) {
            $arguments = ($this->prop)($firstArguments, 'arguments');
            // constructor
            $setters = $this->getSetters($graph, $class, $arguments, $classId);
            $graph->nodes[] = new ClassNode($classId, $class, $setters);

            return;
        }
        $graph->nodes[] = new ClassNode($classId, $class, []);
    }
}
