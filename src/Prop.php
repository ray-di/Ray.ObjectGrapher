<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

use LogicException;
use ReflectionClass;
use ReflectionException;

final class Prop
{
    /**
     * Read object property via reflection
     *
     * @param object|null $object
     * @param string      $prop   property
     *
     * @return mixed|string
     */
    public function __invoke($object, string $prop)
    {
        if (! $object) {
            throw new LogicException();
        }

        try {
            $property = (new ReflectionClass($object::class))->getProperty($prop);
        } catch (ReflectionException) {
            return '';
        }

        return $property->getValue($object);
    }
}
