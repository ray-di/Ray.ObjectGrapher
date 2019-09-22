<?php

namespace Ray\ObjectVisualGrapher;

final class Prop
{
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