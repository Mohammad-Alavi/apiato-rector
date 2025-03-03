<?php

use MohammadAlavi\ApiatoRector\Rules\TransformMethodToResponseCreateRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(TransformMethodToResponseCreateRector::class);
};
