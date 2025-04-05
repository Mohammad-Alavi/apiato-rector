<?php

namespace MohammadAlavi\ApiatoRector\Tests\TransformMethodToResponseFacadeRector\Fixture;

class SomeClass
{
    public function run()
    {
        $this->noContent();
        $this->created();
        $this->accepted();
        $this->deleted();
        $this->created($data);
        $this->accepted($data);
        $this->deleted($data);
        $this->json($data);
        $this->json($data, $status, $headers, $options);
        $this->transform($data, SomeClass::class);
        $this->transform($data, SomeClass::class, $otherParams);
        $this->withMeta($someMeta)->transform($data, new SomeClass());
        $this->withMeta($someMeta)->transform($data, SomeClass::class);
    }
}

?>
-----
<?php

namespace MohammadAlavi\ApiatoRector\Tests\TransformMethodToResponseFacadeRector\Fixture;

class SomeClass
{
    public function run()
    {
        \Apiato\Support\Facades\Response::noContent();
        \Apiato\Support\Facades\Response::created();
        \Apiato\Support\Facades\Response::accepted();
        \Apiato\Support\Facades\Response::accepted();
        \Apiato\Support\Facades\Response::created($data);
        \Apiato\Support\Facades\Response::accepted($data);
        \Apiato\Support\Facades\Response::accepted($data);
        \Apiato\Support\Facades\Response::json($data);
        \Apiato\Support\Facades\Response::json($data, $status, $headers, $options);
        \Apiato\Support\Facades\Response::create($data, SomeClass::class)->toArray();
        \Apiato\Support\Facades\Response::create($data, SomeClass::class)->parseIncludes($otherParams)->toArray();
        \Apiato\Support\Facades\Response::create($data, new SomeClass())->addMeta($someMeta)->toArray();
        \Apiato\Support\Facades\Response::create($data, SomeClass::class)->addMeta($someMeta)->toArray();
    }
}

?>
