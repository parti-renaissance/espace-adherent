<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('sonata_block', [
        'default_contexts' => [
            'cms',
        ],
        'blocks' => [
            'sonata.admin.block.admin_list' => [
                'contexts' => [
                    'admin',
                ],
            ],
        ],
    ]);
};
