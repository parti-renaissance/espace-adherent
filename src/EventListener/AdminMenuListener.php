<?php

namespace App\EventListener;

use Knp\Menu\Provider\MenuProviderInterface;
use Sonata\AdminBundle\Event\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AdminMenuListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly MenuProviderInterface $provider,
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::SIDEBAR => 'onSidebarConfigure',
        ];
    }

    public function onSidebarConfigure(ConfigureMenuEvent $event): void
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN_RENAISSANCE_CREATE_ADHERENT')) {
            return;
        }

        $extras = ['icon' => '<i class="fa fa-folder"></i>'];

        $menu = $event->getMenu();
        $subMenu = $this->provider->get('sonata_group_menu',
            [
                'name' => 'admin.tools',
                'group' => [
                    'label' => 'admin.new.renaissance_adherent.label',
                    'on_top' => false,
                    'translation_domain' => 'messages',
                    'keep_open' => false,
                    'items' => [
                        [
                            'label' => 'admin.new.renaissance_adherent.create.label',
                            'route' => 'admin_app_adherent_create_renaissance_verify_email',
                            'route_params' => null,
                            'route_absolute' => false,
                        ],
                    ],
                ],
            ]
        );

        $subMenu = $menu->addChild($subMenu);
        $subMenu->setExtras(array_merge($subMenu->getExtras(), $extras));
    }
}
