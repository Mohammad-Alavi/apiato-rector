<?php

use Rector\Config\RectorConfig;
use MohammadAlavi\ApiatoRector\Rules\RemoveGroupAttributeRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withImportNames(false, false, false, true)
    ->withRules([
        RemoveGroupAttributeRector::class,
    ]);
// uncomment to reach your current PHP version
// ->withPhpSets()
//    ->withTypeCoverageLevel(0);
