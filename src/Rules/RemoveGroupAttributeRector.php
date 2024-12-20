<?php

namespace MohammadAlavi\ApiatoRector\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPUnit\Framework\Attributes\Group;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RemoveGroupAttributeRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove #[Group] attribute from PHPUnit test classes',
            [],
        );
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): Class_|null
    {
        $hasChanged = false;
        foreach ($node->attrGroups as $groupKey => $attrGroup) {
            foreach ($attrGroup->attrs as $attrKey => $attr) {
                if ($this->isName($attr, Group::class)) {
                    if (1 === count($attrGroup->attrs)) {
                        unset($node->attrGroups[$groupKey]);
                        $hasChanged = true;
                        continue;
                    }

                    unset($node->attrGroups[$groupKey]->attrs[$attrKey]);
                    $hasChanged = true;
                }
            }
        }

        if ($hasChanged) {
            return $node;
        }

        return null;
    }
}
