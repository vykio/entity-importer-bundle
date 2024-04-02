<?php

namespace EntityImporterBundle\Transformer;

use EntityImporterBundle\Model\DirectRelationNode;
use EntityImporterBundle\Model\RawNode;

class RawNodeTransformer
{
    public function transformToInstances(RawNode $object)
    {
        $className = $object->getClassName();
        $instance = new $className();
        $class = new \ReflectionClass($object->getClassName());

        foreach ($object->getData() as $propertyName => $value) {
            $property = $class->getProperty($propertyName);
            $property->setAccessible(true);

            switch (true) {
                case $value instanceof DirectRelationNode:
                    $property->setValue($instance, $this->transformToInstances($value));
                    break;
                default:
                    $property->setValue($instance, $value);
            }

            $property->setAccessible(false);
        }
        return $instance;
    }
}