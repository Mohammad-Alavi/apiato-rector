<?php

declare(strict_types=1);

namespace MohammadAlavi\ApiatoRector\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class TransformMethodToResponseCreateRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Refactor $this-> methods into \Apiato\Support\Facades\Response facade calls.',
            [
                new CodeSample(
                    <<<'CODE_BEFORE'
<?php

class SomeClass
{
    public function run()
    {
        $this->noContent();
        $this->transform($data, SomeClass::class);
    }
}
CODE_BEFORE
                    ,
                    <<<'CODE_AFTER'
<?php

use Apiato\Support\Facades\Response;

class SomeClass
{
    public function run()
    {
        Response::noContent();
        Response::create($data, SomeClass::class)->toArray();
    }
}
CODE_AFTER
                ),
            ],
        );
    }

    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    public function refactor(Node $node): Node|null
    {
        if (!$node instanceof MethodCall) {
            return null;
        }

        // e.g. $this->noContent() => Response::noContent()
        if ($this->isCalledOnThis($node, 'noContent')) {
            return new StaticCall(new Name('\Apiato\Support\Facades\Response'), 'noContent', []);
        }

        // e.g. $this->json() => Response::json()
        if ($this->isCalledOnThis($node, 'json')) {
            return new StaticCall(new Name('\Apiato\Support\Facades\Response'), 'json', $node->args);
        }

        // e.g. $this->created() => Response::created()
        if ($this->isCalledOnThis($node, 'created')) {
            $args = [];
            if (count($node->args) > 0) {
                $args = [$node->args[0]];
            }

            return new StaticCall(new Name('\Apiato\Support\Facades\Response'), 'created', $args);
        }

        // e.g. $this->accepted() => Response::accepted()
        // e.g. $this->deleted() => Response::accepted()
        if ($this->isCalledOnThis($node, 'accepted') || $this->isCalledOnThis($node, 'deleted')) {
            $args = [];
            if (count($node->args) > 0) {
                $args = [$node->args[0]];
            }

            return new StaticCall(new Name('\Apiato\Support\Facades\Response'), 'accepted', $args);
        }

        // e.g. $this->transform($data, SomeClass::class, $maybeInclude)
        if ($this->isName($node->name, 'transform')) {
            return $this->refactorTransformCall($node);
        }

        return null;
    }

    /**
     * We only match $this->methodName() calls.
     */
    private function isCalledOnThis(MethodCall $methodCall, string $name): bool
    {
        if (!$this->isName($methodCall->name, $name)) {
            return false;
        }

        return $methodCall->var instanceof Node\Expr\Variable
            && 'this' === $methodCall->var->name;
    }

    /**
     * Convert:
     *   $this->transform($data, SomeClass::class, $includes)
     * to:
     *   Response::create($data, SomeClass::class)
     *       ->parseIncludes($includes)
     *       ->toArray();
     *
     * Also handle: $this->withMeta($meta)->transform($data, SomeClass::class, ...).
     */
    private function refactorTransformCall(MethodCall $transformCall): Node|null
    {
        $caller = $transformCall->var;
        $maybeMetaCall = null;

        // If preceded by ->withMeta($meta), store that
        if ($caller instanceof MethodCall && $this->isName($caller->name, 'withMeta')) {
            $maybeMetaCall = $caller;
            $caller = $caller->var; // typically $this
        }

        $args = $transformCall->args;
        if (count($args) < 2) {
            // Not enough arguments => skip
            return null;
        }

        [$dataArg, $classArg] = $args;
        $maybeThirdArg = $args[2] ?? null;

        // Response::create($data, $classArg)
        $responseCreate = new StaticCall(new Name('\Apiato\Support\Facades\Response'), 'create', [$dataArg, $classArg]);
        $chainedCall = $responseCreate;

        // If there was withMeta($metaArg), we add ->addMeta($metaArg)
        if ($maybeMetaCall && isset($maybeMetaCall->args[0])) {
            $chainedCall = $this->nodeFactory->createMethodCall(
                $chainedCall,
                'addMeta',
                [$maybeMetaCall->args[0]],
            );
        }

        // If there's a 3rd argument, interpret as ->parseIncludes($thirdArg)
        if (!is_null($maybeThirdArg)) {
            $chainedCall = $this->nodeFactory->createMethodCall(
                $chainedCall,
                'parseIncludes',
                [$maybeThirdArg],
            );
        }

        // Finally chain ->toArray()
        return $this->nodeFactory->createMethodCall($chainedCall, 'toArray');
    }
}
