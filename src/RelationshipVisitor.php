<?php
declare(strict_types = 1);

namespace Audience77\LaravelSchemaspyMeta;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class RelationshipVisitor extends NodeVisitorAbstract
{
    private ?string $foundMethod = null;
    const RELATIONSHIP_METHODS = [
        'hasOne',
        'hasMany',
        'belongsTo',
        'belongsToMany',
        'morphOne',
        'morphMany',
        'morphToMany',
        'morphedByMany',
    ];

    public function enterNode(Node $node)
    {
        if ($node instanceof MethodCall && $node->name instanceof Node\Identifier) {
            $methodName = $node->name->toString();

            if (in_array($methodName, self::RELATIONSHIP_METHODS, true)) {
                $this->foundMethod = $methodName;
                return NodeTraverser::STOP_TRAVERSAL;
            }
        }
    }

    public function getFoundMethod(): ?string
    {
        return $this->foundMethod;
    }
}
