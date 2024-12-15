<?php
declare(strict_types = 1);

namespace Audience77\LaravelSchemaspyMeta;

use PhpParser\ErrorHandler\Collecting;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\ParserFactory;

class PhpAstParser
{
    public ?string $namespace = null;
    public ?string $className = null;
    public ?string $class = null;
    public array $methods = [];

    public function __construct(string $file)
    {
        $statements = $this->parseFile($file);
        if (empty($statements)) {
            return;
        }

        $nameSpaceStatement = $this->getNamespaceStatement($statements);
        if (is_null($nameSpaceStatement)) {
            return;
        }
        $this->namespace = $nameSpaceStatement->name->toString();

        $classStatement = $this->getClassStatement($nameSpaceStatement);
        if (is_null($classStatement)) {
            return;
        }
        $this->className = $classStatement->name->toString();
        $this->class = $this->namespace . '\\' . $this->className;

        $this->methods = $this->getMethodStatements($classStatement);
    }

    /**
     * @param string $file
     * @return Stmt[]|null
     */
    private function parseFile(string $file): ?array
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $code = file_get_contents($file);
        $statements = $parser->parse($code, new Collecting());

        return $statements;
    }

    /**
     * @param Stmt[] $statements
     * @return Namespace_|null
     */
    private function getNamespaceStatement(array $statements): ?Namespace_
    {
        foreach ($statements as $statement) {
            if ($statement instanceof Namespace_) {
                return $statement;
            }
        }

        return null;
    }

    /**
     * @param Namespace_ $nameSpaceStatement
     * @return Class_|null
     */
    private function getClassStatement(Namespace_ $nameSpaceStatement): ?Class_
    {

        foreach ($nameSpaceStatement->stmts as $statement) {
            if ($statement instanceof Class_) {
                return $statement;
            }
        }

        return null;
    }

    /**
     * @param Class_ $classStatement
     * @return ClassMethod[]
     */
    private function getMethodStatements(Class_ $classStatement): array
    {
        $methods = [];

        foreach ($classStatement->stmts as $stmt) {
            if ($stmt instanceof ClassMethod) {
                $methods[] = $stmt;
            }
        }

        return $methods;
    }
}
