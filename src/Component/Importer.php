<?php

namespace EntityImporterBundle\Component;

use EntityImporterBundle\Model\DirectRelationNode;
use EntityImporterBundle\Model\RawNode;
use EntityImporterBundle\Model\RelationNode;
use EntityImporterBundle\Transformer\RawNodeTransformer;
use EntityImporterBundle\Transformer\WorksheetTransformer;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\Yaml\Yaml;

class Importer
{
    private array $structure = [];
    private array $files = [];

    private ExpressionLanguage $expressionLanguage;
    private RawNodeTransformer $nodeTransformer;
    private WorksheetTransformer $worksheetTransformer;

    public function __construct(ExpressionLanguage $expressionLanguage, RawNodeTransformer $nodeTransformer, WorksheetTransformer $worksheetTransformer)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->nodeTransformer = $nodeTransformer;
        $this->worksheetTransformer = $worksheetTransformer;
    }

    public function loadFile(string $filepath): void
    {
        $data = Yaml::parseFile($filepath);

        foreach ($data['__files'] as $alias => $relativePath) {
            $reader = new Xlsx();
            $spreadsheet = $reader->load($relativePath);
            $this->files[$alias] = $this->worksheetTransformer->worksheetToArray($spreadsheet->getSheet(0));
        }

        $this->structure = array_filter($data, fn($index) => $index !== '__files', ARRAY_FILTER_USE_KEY);
    }

    public function getInstances(): array
    {
        $instances = [];
        $formattedStructure = $this->getFormattedStructure();

        foreach ($formattedStructure as $notionClass => $objects) {
            foreach ($objects as $object) {
                if (!$object instanceof RawNode) {
                    continue;
                }

                $instances[$notionClass][] = $this->nodeTransformer->transformToInstances($object);
            }
        }

        return $instances;
    }

    public function getFormattedStructure(): array
    {
        $retrievedData = [];
        foreach ($this->structure as $className => $datum) {
            $fileData = $this->files[$datum['file']];

            foreach ($fileData as $fileLine) {
                $line = $this->getNode($datum['properties'], $fileLine, $className);
                $retrievedData[$className][] = $line;
            }
        }
        return $retrievedData;
    }

    private function getNode($properties, $fileLine, $className, $relation = null): RawNode
    {
        $tmp = [];
        foreach ($properties as $propertyName => $item) {
            if ($item['relation']) {
                $tmp[$propertyName] = $this->getNode($item['relation']['properties'], $fileLine, $item['relation']['class'], $item['relation']['strategy']);
            } else {
                $tmp[$propertyName] = $this->formatProperty($fileLine, $item);
            }
        }

        if ($relation !== null) {
            switch ($relation) {
                case RelationNode::DIRECT:
                    return (new DirectRelationNode($className))->setData($tmp);
            }
        }

        return (new RawNode($className))->setData($tmp);
    }

    private function formatProperty(array $fileLine, array $properties): string
    {
        return match (true) {
            $properties['column'] && $properties['converter'] && str_contains($properties['converter'], '{}') => $this->expressionLanguage->evaluate(str_replace('{}', $fileLine[$properties['column']], $properties['converter'])),
            $properties['column'] => $fileLine[$properties['column']],
            default => "",
        };
    }
}