<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SensiolabsSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Set\TwigSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
                             __DIR__ . '/config',
                             __DIR__ . '/public',
                             __DIR__ . '/src',
                             __DIR__ . '/tests',
                         ]);

    $rectorConfig->sets([
//                            DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
                            SymfonySetList::SYMFONY_52_VALIDATOR_ATTRIBUTES,
//                            SensiolabsSetList::ANNOTATIONS_TO_ATTRIBUTES,
                        ]);
};
