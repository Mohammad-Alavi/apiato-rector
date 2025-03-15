<?php

declare(strict_types=1);

namespace MohammadAlavi\ApiatoRector\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Webmozart\Assert\Assert;

final class RefactorHttpExceptionRector extends AbstractRector implements ConfigurableRectorInterface
{
    private string $parentClass = \MohammadAlavi\ApiatoRector\Tests\RefactorHttpExceptionRector\Fixture\ExtendFrom::class;

    public function configure(array $configuration): void
    {
        Assert::keyExists($configuration, 'parent_class');
        Assert::string($configuration['parent_class']);

        $this->parentClass = $configuration['parent_class'];
    }

    public function getNodeTypes(): array
    {
        return [Class_::class, New_::class];
    }

    public function refactor(Node $node): Node|Class_|array|null
    {
        if ($node instanceof Class_) {
            return $this->refactorClass($node);
        }

        if ($node instanceof New_) {
            return $this->refactorInstantiation($node);
        }

        return null;
    }

    private function refactorClass(Class_ $class): Class_|null
    {
        if ($this->parentClass !== $class->extends->name) {
            return null;
        }

        $code = null;
        $message = null;

        foreach ($class->stmts as $key => $stmt) {
            if ($stmt instanceof Property) {
                $propertyName = $this->getName($stmt->props[0]->name);
                if (null === $stmt->props[0]->default) {
                    continue;
                }
                if ('code' === $propertyName) {
                    if ($stmt->props[0]->default instanceof Node\Scalar\LNumber) {
                        $code = $stmt->props[0]->default->value;
                    } else {
                        $code = $stmt->props[0]->default;
                    }
                    unset($class->stmts[$key]);
                } elseif ('message' === $propertyName) {
                    $message = $stmt->props[0]->default->value;
                    unset($class->stmts[$key]);
                }
            }
        }

        if (null !== $code && null !== $message) {
            $class->stmts[] = $this->createCreateMethod($code, $message);
        }

        return $class;
    }

    private function createCreateMethod(ClassConstFetch|int $defaultCode, string $defaultMessage): ClassMethod
    {
        $statusCodeParam = new Node\Param(
            new Node\Expr\Variable('statusCode'),
            new Node\Expr\ConstFetch(new Node\Name('null')),
            new Node\NullableType(new Node\Name('int')),
        );

        $messageParam = new Node\Param(
            new Node\Expr\Variable('message'),
            new Node\Expr\ConstFetch(new Node\Name('null')),
            new Node\NullableType(new Node\Name('string')),
        );

        $codeArgExpr = is_int($defaultCode)
            ? new Node\Scalar\LNumber($defaultCode)
            : $defaultCode;

        $codeArg = new Node\Arg(new Node\Expr\BinaryOp\Coalesce(
            new Node\Expr\Variable('statusCode'),
            $codeArgExpr,
        ));

        $messageArg = new Node\Arg(new Node\Expr\BinaryOp\Coalesce(
            new Node\Expr\Variable('message'),
            new Node\Scalar\String_($defaultMessage),
        ));

        $staticFactory = new New_(
            new Node\Name('static'),
            [$codeArg, $messageArg],
        );

        $returnStmt = new Node\Stmt\Return_($staticFactory);

        return new ClassMethod('create', [
            'flags' => Class_::MODIFIER_PUBLIC | Class_::MODIFIER_STATIC,
            'params' => [$statusCodeParam, $messageParam],
            'returnType' => new Node\Name('static'),
            'stmts' => [$returnStmt],
        ]);
    }

    private function refactorInstantiation(New_ $node): Node|null
    {
        if (
            $this->isName($node->class, 'static')
            || $this->isName($node->class, 'self')
            || !$this->isObjectType($node->class, new ObjectType($this->parentClass))
        ) {
            return null;
        }

        $args = $node->args;

        $statusCodeArg = $args[1] ?? $this->nodeFactory->createArg(
            new Node\Expr\ConstFetch(new Node\Name('null')),
        );
        $messageArg = $args[0] ?? $this->nodeFactory->createArg(
            new Node\Expr\ConstFetch(new Node\Name('null')),
        );

        $className = $this->getName($node->class);

        return $this->nodeFactory->createStaticCall($className, 'create', [$statusCodeArg, $messageArg]);
    }
}
