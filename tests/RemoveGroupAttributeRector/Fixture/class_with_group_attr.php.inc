<?php

namespace MohammadAlavi\ApiatoRector\Tests\RemoveGroupAttributeRector\Fixture;

use MohammadAlavi\ApiatoRector\Rules\RemoveGroupAttributeRector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

#[Group('ship')]
#[CoversClass(RemoveGroupAttributeRector::class)]
class ClassWithAttribute {}

?>
-----
<?php

namespace MohammadAlavi\ApiatoRector\Tests\RemoveGroupAttributeRector\Fixture;

use MohammadAlavi\ApiatoRector\Rules\RemoveGroupAttributeRector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

#[CoversClass(RemoveGroupAttributeRector::class)]
class ClassWithAttribute {}

?>
