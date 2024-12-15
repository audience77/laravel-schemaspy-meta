<?php
declare(strict_types = 1);

namespace Audience77\LaravelSchemaspyMeta;

class SchemaMeta
{
    const XML_TEMPLATE = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<schemaMeta xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://schemaspy.org/xsd/6/schemameta.xsd" >
    <tables />
</schemaMeta>
XML;

    public static function generate(array $targetAsts, string $xmlFile): void
    {
        $relationshipList = [];
        foreach ($targetAsts as $ast) {
            $relationships = new Relationships($ast);
            $relationshipList = array_merge($relationshipList, $relationships->get());
        }

        $sxe = new \SimpleXMLElement(self::XML_TEMPLATE);
        foreach ($relationshipList as $relationship) {
            self::addRelationshipNodeToXml($sxe->tables, $relationship);
        }

        $dom = self::convertSxeToDom($sxe);

        file_put_contents($xmlFile, $dom->saveXML());
    }

    protected static function addRelationshipNodeToXml(\SimpleXMLElement $sxe, Relationship $relationship): void
    {
        $getTable = function ($sxe) use ($relationship) {
            return $sxe->xpath("table[@name=\"{$relationship->relatedTable}\"]")[0] ?? null;
        };

        $getColumn = function ($sxe) use ($relationship) {
            return $sxe->xpath("column[@name=\"{$relationship->foreignKey}\"]")[0] ?? null;
        };

        $getForeignKey = function ($sxe) use ($relationship) {
            return $sxe->xpath("foreignKey[@table=\"{$relationship->parentTable}\"][@column=\"{$relationship->localKey}\"]")[0] ?? null;
        };

        if (is_null($getTable($sxe))) {
            $sxe->addChild('table')
                ->addAttribute('name', $relationship->relatedTable);
        }

        if (is_null($getColumn($getTable($sxe)))) {
            $getTable($sxe)->addChild('column')
                ->addAttribute('name', $relationship->foreignKey);
        }

        if (is_null($getForeignKey($getColumn($getTable($sxe))))) {
            $node = $getColumn($getTable($sxe))->addChild('foreignKey');
            $node->addAttribute('table', $relationship->parentTable);
            $node->addAttribute('column', $relationship->localKey);
        }
    }

    protected static function convertSxeToDom(\SimpleXMLElement $sxe): \DOMDocument
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($sxe->asXML());

        return $dom;
    }
}
