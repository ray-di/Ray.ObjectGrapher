<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

use Ray\Di\Arguments;
use Ray\Di\Dependency;
use Ray\Di\SetterMethod;

final class MyDependency
{
    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $classId;

    /**
     * @var null|Arguments
     */
    public $arguments;

    /**
     * @var SetterMethod
     */
    public $setterMethods;

    public function __construct(Dependency $dependency)
    {
        $prop = new Prop;
        $newInstance = $prop($dependency, 'newInstance');
        $this->class = $prop($newInstance, 'class');
        $this->classId = (new ClassId)($this->class);
        $this->arguments = $prop($newInstance, 'arguments');
        $this->setterMethods = $prop($newInstance, 'setterMethods');
    }
}
