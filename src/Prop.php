<?php

declare(strict_types=1);

namespace Ray\ObjectGrapher;

final class Prop
{
    /**
     * Set object property accesible
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
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
