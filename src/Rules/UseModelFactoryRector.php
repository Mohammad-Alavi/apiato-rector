<?php

declare(strict_types=1);

namespace MohammadAlavi\ApiatoRector\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

final class UseModelFactoryRector extends AbstractRector implements ConfigurableRectorInterface
{
    private string $userClass = 'App\Containers\AppSection\User\Models\User';
    private string $adminFactoryMethod = 'admin';
    private string $accessSetupMethod = 'setupTestingUserAccess';
    private ValueResolver $valueResolver;

    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }

    public function configure(array $configuration): void
    {
        Assert::keyExists($configuration, 'user_class');
        Assert::string($configuration['user_class']);
        Assert::keyExists($configuration, 'admin_factory_method');
        Assert::string($configuration['admin_factory_method']);
        Assert::keyExists($configuration, 'access_setup_method');
        Assert::string($configuration['access_setup_method']);

        $this->userClass = $configuration['user_class'];
        $this->adminFactoryMethod = $configuration['admin_factory_method'];
        $this->accessSetupMethod = $configuration['access_setup_method'];
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

        if (!$this->isCalledOnThis($node, 'getTestingUser') && !$this->isCalledOnThis($node, 'getTestingUserWithoutAccess')) {
            return null;
        }

        $argCount = count($node->args);

        // Build base static call: User::factory()
        $factoryStaticCall = new StaticCall(
            new FullyQualified($this->userClass),
            'factory'
        );

        if (0 === $argCount) {
            // ->createOne()
            return $this->nodeFactory->createMethodCall($factoryStaticCall, 'createOne');
        }

        // If 1 or more args present:
        // user for ->createOne($args[0]) if it exists
        $createOneArgs = isset($node->args[0]) ? [new Arg($node->args[0]->value)] : [];
        if (1 === $argCount) {
            // ->createOne($arg)
            return $this->nodeFactory->createMethodCall($factoryStaticCall, 'createOne', $createOneArgs);
        }

        // If 2 or 3 arguments => wrap in $this->setupTestingUserAccess(..., $arg2)
        // First param => the factory call
        // If 3rd arg is true => ->admin()->createOne($arg)
        // If 3rd arg is false => ->createOne($arg)
        $factoryCall = $factoryStaticCall;

        if ($this->isCalledOnThis($node, 'getTestingUserWithoutAccess') && 2 === $argCount) {
            $isAdmin = $this->valueResolver->isTrue($node->args[1]->value);
            if ($isAdmin) {
                $factoryCall = $this->nodeFactory->createMethodCall($factoryCall, $this->adminFactoryMethod);
            }

            return $this->nodeFactory->createMethodCall($factoryCall, 'createOne', $createOneArgs);
        }

        if (3 === $argCount) {
            $isAdmin = $this->valueResolver->isTrue($node->args[2]->value);
            if ($isAdmin) {
                $factoryCall = $this->nodeFactory->createMethodCall($factoryCall, $this->adminFactoryMethod);
            }
        }
        $factoryCall = $this->nodeFactory->createMethodCall($factoryCall, 'createOne', $createOneArgs);

        return $this->nodeFactory->createMethodCall(
            new Variable('this'),
            $this->accessSetupMethod,
            [
                new Arg($factoryCall),
                // $arg2 is always the second arg
                new Arg($node->args[1]->value),
            ]
        );
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

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Refactor $this->getTestingUser(...) calls to Laravel factory style testing',
            []
        );
    }
}
