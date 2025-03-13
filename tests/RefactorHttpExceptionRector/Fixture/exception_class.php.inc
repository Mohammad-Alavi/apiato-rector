<?php

namespace MohammadAlavi\ApiatoRector\Tests\RefactorHttpExceptionRector\Fixture;

use Symfony\Component\HttpFoundation\Response;

class ExtendFrom {}
class AccessDeniedHttpException extends ExtendFrom
{
    protected $code = Response::HTTP_FORBIDDEN;
    protected $message = 'This action is unauthorized.';
}

?>
-----
<?php

namespace MohammadAlavi\ApiatoRector\Tests\RefactorHttpExceptionRector\Fixture;

use Symfony\Component\HttpFoundation\Response;

class ExtendFrom {}
class AccessDeniedHttpException extends ExtendFrom
{
    public static function create(?int $statusCode = null, ?string $message = null): \Symfony\Component\HttpKernel\Exception\HttpException
    {
        return static::fromStatusCode($statusCode ?? Response::HTTP_FORBIDDEN, $message ?? 'This action is unauthorized.');
    }
}

?>
