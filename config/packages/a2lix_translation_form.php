<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('a2lix_translation_form', [
        'locales' => '%locales%',
        'required_locales' => [
            '%locale%',
        ],
        'templating' => 'admin/form_translations.html.twig',
    ]);
};
