<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('scheb_two_factor', [
        'google' => [
            'enabled' => true,
            'issuer' => 'En Marche !',
            'template' => 'security/admin_google_authenticator.html.twig',
        ],
        'security_tokens' => [
            Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken::class,
        ],
    ]);
};
