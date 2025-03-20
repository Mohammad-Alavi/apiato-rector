<?php

use MohammadAlavi\ApiatoRector\Rules\UseModelFactoryRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UseModelFactoryRector::class);
};
