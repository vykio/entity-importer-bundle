<?php

namespace EntityImporterBundle\Model;

class DirectRelationNode extends RelationNode
{
    public function __construct(string $className)
    {
        parent::__construct($className, self::DIRECT);
    }
}