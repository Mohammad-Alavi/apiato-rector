<?php

namespace MohammadAlavi\ApiatoRector\Tests\UseModelFactoryRector\Fixture;

class TestCase{}

class CustomMethodToFactoryTest extends TestCase
{
    public function testSomething(): void
    {
        $this->getTestingUser();
        $this->getTestingUser($arg);
        $this->getTestingUser($arg, $arg2);
        $this->getTestingUser($arg, $arg2, true);
        $this->getTestingUser($arg, $arg2, false);
        $this->getTestingUserWithoutAccess();
        $this->getTestingUserWithoutAccess($arg);
        $this->getTestingUserWithoutAccess($arg, true);
        $this->getTestingUserWithoutAccess($arg, false);
    }
}
?>
-----
<?php

namespace MohammadAlavi\ApiatoRector\Tests\UseModelFactoryRector\Fixture;

class TestCase{}

class CustomMethodToFactoryTest extends TestCase
{
    public function testSomething(): void
    {
        \App\Containers\AppSection\User\Models\User::factory()->createOne();
        \App\Containers\AppSection\User\Models\User::factory()->createOne($arg);
        $this->setupTestingUserAccess(\App\Containers\AppSection\User\Models\User::factory()->createOne($arg), $arg2);
        $this->setupTestingUserAccess(\App\Containers\AppSection\User\Models\User::factory()->admin()->createOne($arg), $arg2);
        $this->setupTestingUserAccess(\App\Containers\AppSection\User\Models\User::factory()->createOne($arg), $arg2);
        \App\Containers\AppSection\User\Models\User::factory()->createOne();
        \App\Containers\AppSection\User\Models\User::factory()->createOne($arg);
        \App\Containers\AppSection\User\Models\User::factory()->admin()->createOne($arg);
        \App\Containers\AppSection\User\Models\User::factory()->createOne($arg);
    }
}
?>
