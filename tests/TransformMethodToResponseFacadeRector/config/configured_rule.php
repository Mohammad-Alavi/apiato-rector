<?php

use MohammadAlavi\ApiatoRector\Rules\TransformMethodToResponseFacadeRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(TransformMethodToResponseFacadeRector::class);
};
