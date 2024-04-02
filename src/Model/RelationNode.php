<?php

namespace EntityImporterBundle\Model;

class RelationNode extends RawNode
{
    public const DIRECT = "direct";

    private string $relation;

    public function __construct(string $className, string $relation)
    {
        $this->relation = $relation;
        parent::__construct($className);
    }

    public function getRelation(): string
    {
        return $this->relation;
    }
}