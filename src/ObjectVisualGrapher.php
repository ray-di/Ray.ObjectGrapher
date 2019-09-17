<?php

declare(strict_types=1);

namespace Ray\ObjectVisualGrapher;

use Ray\Di\AbstractModule;
use Ray\Di\Argument;
use Ray\Di\Dependency;
use Ray\Di\DependencyProvider;
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
        [$nodes, $arrows] = $this->getNodes();

        return $this->toString($nodes, $arrows);
    }
    
    public function getNodes() : array
    {
        $nodes = $arrows = [];
        foreach ($this->container as $dependencyIndex => $dependency) {
            [$interface, $name] = \explode('-', $dependencyIndex);
            $interfaceId = $this->getInterfaceId($interface, $name);
            $nodes[] = new InterfaceNode($interfaceId, $interface, $name);
            if ($dependency instanceof Dependency) {
                $this->getDependencyNode($interfaceId, $dependency, $nodes, $arrows);
            }
            if ($dependency instanceof DependencyProvider) {
                $this->getProviderNode($interfaceId, $dependency, $nodes, $arrows);
            }
        }
        return [$nodes, $arrows];
    }

    private function getInterfaceId(string $interace, string $name)
    {
        return sprintf('i_%s_%s', $this->name($interace), $this->name($name));
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
        $property = (new \ReflectionClass(get_class($object)))->getProperty($prop);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    public function getDependencyNode(string $interfaceId, Dependency $dependency, array &$nodes, array &$arrows) : void
    {
        $newInstance = $this->prop($dependency, 'newInstance');
        $class = $this->prop($newInstance, 'class');
        $classId = $this->getClassId($class);
        $nodes[] = new ClassNode($classId, $class, ['construct' => null]);
        $arrows[] = new ToClass($interfaceId, $classId);
        // constructor
        $firstArguments = $this->prop($newInstance, 'arguments');
        if ($firstArguments) {
            $arguments = $this->prop($firstArguments, 'arguments');
            // constructor
            $setters = $this->getSetters($arrows, $class, $arguments, $classId);
            $nodes[] = new ClassNode($classId, $class, $setters);
            return;
        }
        $nodes[] = new ClassNode($classId, $class, []);
    }

    public function getProviderNode(string $interfaceId, $dependency, array &$nodes, array &$arrows) : void
    {
        $dependency = $this->prop($dependency, 'dependency');
        $newInstance = $this->prop($dependency, 'newInstance');
        $class = $this->prop($newInstance, 'class');
        $classId = $this->getClassId($class);
        $firstArguments = $this->prop($newInstance, 'arguments');
        if ($firstArguments) {
            $arguments = $this->prop($firstArguments, 'arguments');
            $setters = $this->getSetters($arrows, $class, $arguments, $classId);
            $nodes[] = new ProviderNode($classId, $class, $setters);
            $arrows[] = new ToProvider($interfaceId, $classId);
            return;
        }
        $nodes[] = new ClassNode($classId, $class, []);
    }

    public function name($class) : string
    {
        return str_replace('\\', '_', $class);
    }

    public function getSetters(array &$arrows, $class, $arguments, string $classId): array
    {
        $port = sprintf('p_%s_construct', $this->name($class));
        $setters = ['constrcut' => $port];
        foreach ($arguments as $argument) {
            /** @var Argument $arugment */
            $dependencyIndex = $this->prop($argument, 'index');
            [$interface, $name] = \explode('-', $dependencyIndex);
            $classPort = sprintf('%s:%s:e', $classId, $port);
            $arrows[] = new Arrow($classPort, $this->getInterfaceId($interface, $name), $interface);
        }

        return $setters;
    }
}
