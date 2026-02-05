<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('sonata.exporter.writer.csv.delimiter', ';');

    $parameters->set('sonata.exporter.writer.csv.with_bom', true);

    $containerConfigurator->extension('sonata_exporter', [
        'exporter' => [
            'default_writers' => [
                'csv',
                'xlsx',
            ],
        ],
    ]);
};
