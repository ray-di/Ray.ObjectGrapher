<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

final class Prop
{
    /**
     * Read object property via reflection
     *
     * @param null|object $object
     * @param string      $prop   property
     *
     * @return mixed|string
     */
    public function __invoke($object, string $prop)
    {
        if (! $object) {
            throw new \LogicException();
        }
        try {
            $property = (new \ReflectionClass(get_class($object)))->getProperty($prop);
        } catch (\ReflectionException $e) {
            return '';
        }

        return $property->getValue($object);
    }
}
