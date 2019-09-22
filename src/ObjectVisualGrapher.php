<?php

declare(strict_types=1);

namespace Ray\ObjectVisualGrapher;

use PhpParser\Node\Expr\BinaryOp\Greater;
use Ray\Di\AbstractModule;
use Ray\Di\Argument;
use Ray\Di\Dependency;
use Ray\Di\DependencyInterface;
use Ray\Di\DependencyProvider;
use Ray\Di\Instance;
use function var_dump;

class ObjectVisualGrapher
{
    /**
     * @var array|\Ray\Di\Dependency[]
     */
    private $container;

    public function __construct(AbstractModule $module)
    {
        $this->container = $module->getContainer()->getContainer();
    }

    public function __invoke() :string
    {
        $graph = $this->getGraph();

        return $this->toString($graph->nodes, $graph->arrows);
    }

    public function getGraph() : Graph
    {
        $graph = new Graph;
        foreach ($this->container as $dependencyIndex => $dependency) {
            [$type, $name] = \explode('-', $dependencyIndex);
            $this->setGraph($graph,$type, $name, $dependency);
        }

        return $graph;
    }

    private function setGraph(Graph $graph, $type, $name, DependencyInterface $dependency): void
    {
        $isTargetBinding = !interface_exists($type);
        $dependencyId = $this->getDependencyId($type, $name);
        if (!$isTargetBinding) {
            $graph->nodes[] = new InterfaceNode($dependencyId, $type, $name);
        }
        if ($dependency instanceof Dependency) {
            $this->dependencyNode($dependencyId, $dependency, $graph, $isTargetBinding);
        }
        if ($dependency instanceof DependencyProvider) {
            $this->getProviderNode($dependencyId, $dependency, $graph);
        }
    }

    private function getDependencyId(string $interace, string $name) : string
    {
        return sprintf('t_%s_%s', $this->name($interace), $this->name($name));
    }

    private function getClassId(string $class)
    {
        return 'c_' . $this->name($class);
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
$string
}
EOT;
    }

    private function prop($object, string $prop)
    {
        if (! $object) {
            throw new \LogicException();
        }
        try {
            $property = (new \ReflectionClass(get_class($object)))->getProperty($prop);
        } catch (\ReflectionException $e) {
            return null;
        }
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    public function dependencyNode(string $interfaceId, DependencyInterface $dependency, Graph $graph, bool $isTargetBinding) : void
    {
        if ($dependency instanceof Instance || $dependency instanceof DependencyProvider) {
            return;
        }
        $newInstance = $this->prop($dependency, 'newInstance');
        $class = $this->prop($newInstance, 'class');
        $classId = $this->getClassId($class);
        $nodes[] = new ClassNode($classId, $class, ['construct' => null]);
        if (! $isTargetBinding) {
            $graph->arrows[] = new ToClass($interfaceId, $classId);
        }
        // constructor
        $firstArguments = $this->prop($newInstance, 'arguments');
        if ($firstArguments) {
            $arguments = $this->prop($firstArguments, 'arguments');
            // constructor
            $setters = $this->getSetters($graph, $class, $arguments, $classId);
            $graph->nodes[] = new ClassNode($classId, $class, $setters);

            return;
        }
        $nodes[] = new ClassNode($classId, $class, []);
    }

    public function getProviderNode(string $interfaceId, $dependency, Graph $graph) : void
    {
        $dependency = $this->prop($dependency, 'dependency');
        $newInstance = $this->prop($dependency, 'newInstance');
        $class = $this->prop($newInstance, 'class');
        $classId = $this->getClassId($class);
        $firstArguments = $this->prop($newInstance, 'arguments');
        if ($firstArguments) {
            $arguments = $this->prop($firstArguments, 'arguments');
            $setters = $this->getSetters($graph, $class, $arguments, $classId);
            $nodes[] = new ProviderNode($classId, $class, $setters);
            if ($interfaceId) {
                $graph->arrows[] = new ToProvider($interfaceId, $classId);
            }
            return;
        }
        $nodes[] = new ClassNode($classId, $class, []);
    }

    public function name($class) : string
    {
        return str_replace('\\', '_', $class);
    }

    public function getSetters(Graph $graph, $class, $arguments, string $classId): array
    {
        $port = sprintf('p_%s_construct', $this->name($class));
        $setters = ['construct' => $port];
        foreach ($arguments as $argument) {
            /** @var Argument $arugment */
            $dependencyIndex = $this->prop($argument, 'index');
            $classPort = sprintf('%s:%s:e', $classId, $port);
            $this->setterArrow($graph, $classPort, $dependencyIndex);
        }

        return $setters;
    }

    public function setterArrow(Graph $graph, string $classPort, string $dependencyIndex)
    {
        [$type, $name] = \explode('-', $dependencyIndex);

        $dependencyId = $this->getDependencyId($type, $name);
        $graph->arrows[] = new Arrow($classPort, $dependencyId, $type);
        if (! isset($this->container[$dependencyIndex])) {
            $graph->nodes[] = new InstanceNode($dependencyId, '<instance>', $name);
        }
//            $this->setGraph($graph, $type, $name, $dependency);
//            $this->dependencyNode($dependencyId, $dependency, $graph, false);
    }
}
