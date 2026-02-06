<?php

declare(strict_types=1);

return static function (Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->add('app_health_check', '/health/ready')
        ->controller(App\Controller\HealthCheckController::class);

    $routingConfigurator->add('logout', '/deconnexion')
        ->host('{app_domain}')
        ->controller('App\Controller\Renaissance\SecurityController::logoutAction')
        ->defaults([
            'app_domain' => '%user_vox_host%',
        ])
        ->methods([
            'GET',
        ])
        ->requirements([
            'app_domain' => '%admin_renaissance_host%|%user_vox_host%',
        ]);

    $routingConfigurator->add('app_user_get_magic_link', '/demander-un-lien-magique')
        ->host('{app_domain}')
        ->controller('App\Controller\Renaissance\MagicLinkController::getMagicLinkAction')
        ->defaults([
            'app_domain' => '%user_vox_host%',
        ])
        ->methods([
            'GET',
            'POST',
        ])
        ->requirements([
            'app_domain' => '%app_renaissance_host%|%user_vox_host%',
        ]);

    $routingConfigurator->add('app_user_connect_with_magic_link', '/connexion-avec-un-lien-magique')
        ->host('{app_domain}')
        ->controller('App\Controller\Renaissance\MagicLinkController::connectViaMagicLinkAction')
        ->defaults([
            'app_domain' => '%user_vox_host%',
        ])
        ->methods([
            'GET',
            'POST',
        ])
        ->requirements([
            'app_domain' => '%app_renaissance_host%|%user_vox_host%',
        ]);

    $routingConfigurator->import('../src/Controller/AssetsController.php', 'attribute')
        ->defaults([
            'app_domain' => '%user_vox_host%',
        ])
        ->requirements([
            'app_domain' => '%app_host%|%app_renaissance_host%|%national_event_host%|%user_vox_host%|%admin_renaissance_host%',
        ])
        ->host('{app_domain}');

    $routingConfigurator->import('../src/Controller/MoocController.php', 'attribute');

    $routingConfigurator->import('../src/Controller/UploadDocumentController.php', 'attribute');

    $routingConfigurator->import('../src/Controller/Api', 'attribute')
        ->prefix('%api_path_prefix%')
        ->defaults([
            '_format' => 'json',
            'app_domain' => '%api_renaissance_host%',
        ])
        ->requirements([
            'app_domain' => '%app_host%|%app_renaissance_host%|%api_renaissance_host%',
        ])
        ->host('{app_domain}');

    $routingConfigurator->import('.', 'api_platform')
        ->prefix('%api_path_prefix%');

    $routingConfigurator->import('../src/Controller/OAuth', 'attribute')
        ->prefix('/oauth/v2')
        ->defaults([
            'app_domain' => '%user_vox_host%',
        ])
        ->requirements([
            'app_domain' => '%app_renaissance_host%|%api_renaissance_host%|%admin_renaissance_host%|%user_vox_host%',
        ])
        ->host('{app_domain}');

    $routingConfigurator->import('@SonataAdminBundle/Resources/config/routing/sonata_admin.php')
        ->host('%admin_renaissance_host%');

    $routingConfigurator->import('.', 'sonata_admin')
        ->host('%admin_renaissance_host%');

    $routingConfigurator->import('../src/Controller/Admin', 'attribute')
        ->host('%admin_renaissance_host%');

    $routingConfigurator->add('lexik_paybox_ipn', '/paybox/payment-ipn/{time}')
        ->host('%webhook_renaissance_host%')
        ->controller('lexik_paybox.controller.default')
        ->methods([
            'GET',
            'POST',
        ]);

    $routingConfigurator->import('../src/Controller/EnMarche', 'attribute')
        ->host('%app_host%');

    $routingConfigurator->import('../src/Controller/EnMarche/VotingPlatform', 'attribute')
        ->prefix('/elections/{uuid}', false)
        ->requirements([
            'uuid' => '%pattern_uuid%',
        ]);

    $routingConfigurator->import('../src/Controller/Procuration', 'attribute')
        ->host('%procuration_host%');

    $routingConfigurator->import('../src/Controller/IntlController.php', 'attribute')
        ->prefix('%api_path_prefix%')
        ->defaults([
            'app_domain' => '%app_host%',
        ])
        ->requirements([
            'app_domain' => '%app_host%|%procuration_host%|%app_renaissance_host%',
        ])
        ->host('{app_domain}');

    $routingConfigurator->import('../src/Controller/Webhook', 'attribute')
        ->host('%webhook_renaissance_host%');

    $routingConfigurator->import('../src/Controller/Renaissance', 'attribute')
        ->host('%user_vox_host%');

    $routingConfigurator->add('app_national_event_redirect', '/{slug}')
        ->host('%national_event_host%')
        ->controller(Symfony\Bundle\FrameworkBundle\Controller\RedirectController::class)
        ->defaults([
            'route' => 'app_national_event_by_slug',
            'slug' => 'slug',
            'keepQueryParams' => true,
        ])
        ->requirements([
            'slug' => '[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*',
        ]);

    $routingConfigurator->import('../src/Controller/Renaissance/NationalEvent', 'attribute')
        ->prefix('/grand-rassemblement')
        ->defaults([
            'app_domain' => '%user_vox_host%',
        ])
        ->requirements([
            'app_domain' => '%national_event_host%|%user_vox_host%',
        ])
        ->host('{app_domain}');

    $routingConfigurator->add('app_national_event_ticket', '/{file}')
        ->host('%national_event_ticket_host%')
        ->requirements([
            'file' => '%pattern_uuid%\..+',
        ]);

    $routingConfigurator->add('renaissance_site', '/')
        ->host('%renaissance_host%');

    $routingConfigurator->add('legislative_site', '/')
        ->host('%legislative_host%');

    $routingConfigurator->add('vox_app', '/')
        ->host('%vox_host%');

    $routingConfigurator->add('vox_app_redirect', '/app')
        ->host('%user_vox_host%')
        ->controller(App\Controller\OAuth\RedirectAppController::class)
        ->methods([
            'GET',
        ]);

    $routingConfigurator->add('vox_app_redirect_from_admin', '/vox')
        ->host('%admin_renaissance_host%')
        ->controller(App\Controller\OAuth\RedirectAppController::class)
        ->methods([
            'GET',
        ]);

    $routingConfigurator->add('cadre_app_redirect', '/cadre')
        ->host('{app_domain}')
        ->controller(App\Controller\OAuth\RedirectAppController::class)
        ->defaults([
            'clientCode' => 'jemengage_web',
            'app_domain' => '%user_vox_host%',
        ])
        ->methods([
            'GET',
        ])
        ->requirements([
            'app_domain' => '%admin_renaissance_host%|%user_vox_host%',
        ]);

    $routingConfigurator->add('elecmap_app_redirect', '/elecmap')
        ->host('{app_domain}')
        ->controller(App\Controller\OAuth\RedirectAppController::class)
        ->defaults([
            'clientCode' => 'eaggle',
            'app_domain' => '%user_vox_host%',
        ])
        ->methods([
            'GET',
        ])
        ->requirements([
            'app_domain' => '%admin_renaissance_host%|%user_vox_host%',
        ]);

    $routingConfigurator->add('formation_auth_start_redirect', '/formation')
        ->host('%user_vox_host%')
        ->controller(Symfony\Bundle\FrameworkBundle\Controller\RedirectController::class)
        ->defaults([
            'path' => '%formation_auth_start_url%',
        ])
        ->methods([
            'GET',
        ]);

    $routingConfigurator->add('formation_app_redirect', '/formation-auth')
        ->host('%user_vox_host%')
        ->controller(App\Controller\OAuth\RedirectAppController::class)
        ->defaults([
            'clientCode' => 'formation',
        ])
        ->methods([
            'GET',
        ]);

    $routingConfigurator->add('user_renaissance_redirect', '/')
        ->host('%user_vox_host%')
        ->controller(Symfony\Bundle\FrameworkBundle\Controller\RedirectController::class)
        ->defaults([
            'route' => 'app_renaissance_login',
        ]);

    $routingConfigurator->add('renaissance_qr_code', '/{uuid}')
        ->host('%renaissance_qrcode_host%')
        ->controller(App\Controller\EnMarche\QrCodeController::class)
        ->methods([
            'GET',
        ]);

    $routingConfigurator->add('app_validate_email', '/api/validate-email')
        ->host('{app_domain}')
        ->controller(App\Controller\Renaissance\Adhesion\Api\ValidateEmailController::class)
        ->defaults([
            'app_domain' => '%app_host%',
        ])
        ->methods([
            'POST',
        ])
        ->requirements([
            'app_domain' => '%app_renaissance_host%|%national_event_host%|%procuration_host%|%user_vox_host%',
        ]);

    $routingConfigurator->add('app_zone_autocomplete', '/api/zone/autocomplete')
        ->host('{app_domain}')
        ->controller(App\Controller\Api\Zone\ZoneAutocompleteController::class)
        ->defaults([
            'app_domain' => '%procuration_host%',
        ])
        ->methods([
            'GET',
        ])
        ->requirements([
            'app_domain' => '%procuration_host%|%api_renaissance_host%',
        ]);

    $routingConfigurator->add('app_renaissance_newsletter_confirm', '/newsletter/confirmation/{uuid}/{confirm_token}')
        ->host('{app_domain}')
        ->controller(App\Controller\Renaissance\Newsletter\ConfirmNewsletterController::class)
        ->defaults([
            'app_domain' => '%user_vox_host%',
        ])
        ->methods([
            'GET',
        ])
        ->requirements([
            'app_domain' => '%national_event_host%|%user_vox_host%',
        ]);

    $routingConfigurator->import('../src/Controller/EnMarche/UserController.php', 'attribute')
        ->defaults([
            'app_domain' => '%app_host%',
        ])
        ->requirements([
            'app_domain' => '%app_host%|%app_renaissance_host%',
        ])
        ->host('{app_domain}');

    $routingConfigurator->import('../src/Controller/EnMarche/CertificationRequestController.php', 'attribute')
        ->defaults([
            'app_domain' => '%app_host%',
        ])
        ->requirements([
            'app_domain' => '%app_host%|%app_renaissance_host%',
        ])
        ->host('{app_domain}');

    $routingConfigurator->add('robots', '/robots.txt')
        ->host('{app_domain}')
        ->controller(App\Controller\RobotsController::class)
        ->defaults([
            'app_domain' => '%user_vox_host%',
        ])
        ->requirements([
            'app_domain' => '%user_vox_host%|%admin_renaissance_host%',
        ]);

    $routingConfigurator->add('app_renaissance_event_show', '/app/evenements/{slug}')
        ->host('%user_vox_host%')
        ->controller('App\Controller\OAuth\RedirectAppController::redirectToState');

    $routingConfigurator->add('vox_app_profile', '/app/profil')
        ->host('%user_vox_host%')
        ->controller('App\Controller\OAuth\RedirectAppController::redirectToState');

    $routingConfigurator->add('vox_app_elect', '/app/profil/informations-elu')
        ->host('%user_vox_host%')
        ->controller('App\Controller\OAuth\RedirectAppController::redirectToState');

    $routingConfigurator->add('app_renaissance_path', '/app/{path}')
        ->host('%user_vox_host%')
        ->controller('App\Controller\OAuth\RedirectAppController::redirectToState')
        ->requirements([
            'path' => '.+',
        ]);
};
