<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('sonata_admin', [
        'title_logo' => 'logo/small_bg_white.jpg',
        'show_mosaic_button' => false,
        'templates' => [
            'layout' => 'admin/layout.html.twig',
            'form_theme' => [
                'form_theme_admin.html.twig',
            ],
        ],
        'security' => [
            'handler' => 'sonata.admin.security.handler.role',
        ],
        'persist_filters' => true,
        'options' => [
            'html5_validate' => false,
            'default_admin_route' => 'edit',
        ],
        'default_admin_services' => [
            'label_translator_strategy' => 'sonata.admin.label.strategy.noop',
        ],
    ]);
};
