<?php

use MohammadAlavi\ApiatoRector\Rules\RefactorHttpExceptionRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RefactorHttpExceptionRector::class);
};
