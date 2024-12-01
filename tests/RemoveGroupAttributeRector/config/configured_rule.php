<?php

use MohammadAlavi\ApiatoRector\Rules\RemoveGroupAttributeRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveGroupAttributeRector::class);
};
