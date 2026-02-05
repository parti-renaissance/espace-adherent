<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('sonata_doctrine_orm_admin', [
        'templates' => [
            'types' => [
                'list' => [
                    'array' => 'admin/CRUD/list_array.html.twig',
                    'array_list' => 'admin/CRUD/list/array_list.html.twig',
                    'datetime' => 'admin/CRUD/list_datetime.html.twig',
                    'trans' => 'admin/CRUD/list_trans.html.twig',
                    'thumbnail' => 'admin/list/list_thumbnail.html.twig',
                    'color' => 'admin/list/list_color.html.twig',
                ],
                'show' => [
                    'array' => 'admin/CRUD/show_array.html.twig',
                    'array_list' => 'admin/CRUD/show/array_list.html.twig',
                ],
            ],
        ],
    ]);
};
