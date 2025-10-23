<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    'prefix' => 'WpApp\\Vendor',
    'output-dir' => 'vendor-prefixed',
    'finders' => [
        Finder::create()
            ->files()
            ->in('vendor/thecodingmachine/safe')
            ->name(['*.php']),
        Finder::create()
            ->files()
            ->in('vendor/inpsyde/wonolog')
            ->name(['*.php']),
        Finder::create()
            ->files()
            ->in('vendor/psr/log')
            ->name(['*.php']),
        Finder::create()
            ->files()
            ->in('vendor/monolog/monolog')
            ->name(['*.php']),
    ],
    'exclude-namespaces' => [],
    'exclude-classes' => [],
    'exclude-functions' => [],
    'patchers' => [],
];
