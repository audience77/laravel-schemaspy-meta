<?php
declare(strict_types = 1);

namespace Audience77\LaravelSchemaspyMeta;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;

class Relationships
{
    private string $class;
    private array $relationships = [];

    public function __construct(PhpAstParser $ast)
    {
        $this->class = $ast->class;
        foreach ($ast->methods as $method) {
            $this->makeRelationship($method);
        }
    }

    /**
     * @return Relationship[]
     */
    public function get(): array
    {
        return $this->relationships;
    }

    private function getRelationshipType(ClassMethod $method): ?string
    {
        $traverser = new NodeTraverser();
        $visitor = new RelationshipVisitor();
        $traverser->addVisitor($visitor);
        $traverser->traverse($method->stmts);

        return $visitor->getFoundMethod();
    }

    private function makeRelationship(ClassMethod $method): void
    {
        $relationshipType = $this->getRelationshipType($method);
        if (is_null($relationshipType)) {
            return;
        }

        $parentModel = new $this->class();
        $methodName = $method->name->toString();

        $relation = $parentModel->$methodName();
        $relatedTable = $relation->getRelated()->getTable();
        $parentTable = $parentModel->getTable();

        $this->handleRelationship($relationshipType, $relation, $relatedTable, $parentTable);
    }

    private function handleRelationship(
        string $relationshipType,
        Relation $relation,
        string $relatedTable,
        string $parentTable
    ): void {
        switch ($relationshipType) {
            case 'hasOne':
            case 'hasMany':
            case 'morphOne':
            case 'morphMany':
                $this->handleParentToChild($relation, $relatedTable, $parentTable);
                break;
            case 'belongsTo':
                $this->handleBelongsTo($relation, $relatedTable, $parentTable);
                break;
            case 'belongsToMany':
            case 'morphToMany':
            case 'morphedByMany':
                $this->handleManyToMany($relation, $relatedTable, $parentTable);
                break;
        }
    }

    private function handleParentToChild(Relation $relation, string $relatedTable, string $parentTable): void
    {
        $foreignKey = $relation->getForeignKeyName();
        $arr = explode('.', $relation->getQualifiedParentKeyName());
        $localKey = end($arr);
        $this->relationships[] = new Relationship($relatedTable, $foreignKey, $parentTable, $localKey);
    }

    private function handleBelongsTo(BelongsTo $relation, string $relatedTable, string $parentTable): void
    {
        $foreignKey = $relation->getForeignKeyName();
        $localKey = $relation->getOwnerKeyName();
        $this->relationships[] = new Relationship($parentTable, $foreignKey, $relatedTable, $localKey);
    }

    private function handleManyToMany(Relation $relation, string $relatedTable, string $parentTable): void
    {
        $intermediateTable = $relation->getTable();
        $foreignPivotKey = $relation->getForeignPivotKeyName();
        $relatedPivotKey = $relation->getRelatedPivotKeyName();
        $arr = explode('.', $relation->getQualifiedParentKeyName());
        $parentLocalKey = end($arr);

        $relationPropRef = (new \ReflectionClass($relation))->getProperty('relatedKey');
        $relationPropRef->setAccessible(true);
        $relatedLocalKey = $relationPropRef->getValue($relation);
        $this->relationships[] = new Relationship($intermediateTable, $foreignPivotKey, $parentTable, $parentLocalKey);
        $this->relationships[] = new Relationship($intermediateTable, $relatedPivotKey, $relatedTable, $relatedLocalKey);
    }
}
