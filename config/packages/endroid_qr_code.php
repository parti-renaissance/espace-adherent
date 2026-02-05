<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('endroid_qr_code', [
        'default' => [
            'writer' => Endroid\QrCode\Writer\PngWriter::class,
            'size' => 300,
            'margin' => 10,
            'encoding' => 'UTF-8',
            'errorCorrectionLevel' => 'low',
            'validateResult' => false,
        ],
    ]);
};
