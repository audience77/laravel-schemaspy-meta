<?php
declare(strict_types = 1);

namespace Audience77\LaravelSchemaspyMeta;

class Relationship
{
    public string $relatedTable;
    public string $foreignKey;
    public string $parentTable;
    public string $localKey;

    public function __construct(string $relatedTable, string $foreignKey, string $parentTable, string $localKey)
    {
        $this->relatedTable = $relatedTable;
        $this->foreignKey = $foreignKey;
        $this->parentTable = $parentTable;
        $this->localKey = $localKey;
    }
}
