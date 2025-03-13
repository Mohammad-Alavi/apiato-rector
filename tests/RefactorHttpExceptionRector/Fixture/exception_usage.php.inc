<?php

namespace MohammadAlavi\ApiatoRector\Tests\RefactorHttpExceptionRector\Fixture;

class ExtendFrom {}
class SomeClass extends ExtendFrom {}
class AnotherClass {}

class Holder
{
    public function aFunction(){
        throw new SomeClass();
        throw new AnotherClass();
        throw new SomeClass('message', 404);
        throw new SomeClass('message');
    }
}

?>
-----
<?php

namespace MohammadAlavi\ApiatoRector\Tests\RefactorHttpExceptionRector\Fixture;

class ExtendFrom {}
class SomeClass extends ExtendFrom {}
class AnotherClass {}

class Holder
{
    public function aFunction(){
        throw \MohammadAlavi\ApiatoRector\Tests\RefactorHttpExceptionRector\Fixture\SomeClass::create(null, null);
        throw new AnotherClass();
        throw \MohammadAlavi\ApiatoRector\Tests\RefactorHttpExceptionRector\Fixture\SomeClass::create(404, 'message');
        throw \MohammadAlavi\ApiatoRector\Tests\RefactorHttpExceptionRector\Fixture\SomeClass::create(null, 'message');
    }
}

?>
