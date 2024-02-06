<?php

namespace App\EventListener;

use Knp\Menu\ItemInterface;
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
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN_RENAISSANCE_CREATE_ADHERENT')) {
            $this->addCreateAdherentMenu($event);
        }

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN_STATS_ALL')) {
            $this->addStatsMenu($event);
        }
    }

    private function addCreateAdherentMenu(ConfigureMenuEvent $event): void
    {
        $this->addItem($event->getMenu(), 'admin.new.renaissance_adherent.label', [
            [
                'label' => 'admin.new.renaissance_adherent.create.label',
                'route' => 'admin_app_adherent_create_renaissance_verify_email',
                'route_params' => null,
                'route_absolute' => false,
            ],
        ]);
    }

    private function addStatsMenu(ConfigureMenuEvent $event): void
    {
        $items = [];

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN_ADHERENT_STATS')) {
            $items[] = [
                'label' => 'Adhésions par département',
                'route' => 'admin_app_stats_adhesion_per_department',
                'route_params' => null,
                'route_absolute' => false,
            ];
        }

        $this->addItem($event->getMenu(), 'Stats', $items, 'fa fa-bar-chart', true);
    }

    private function addItem(ItemInterface $menu, string $groupLabel, array $items, string $groupIcon = 'fa fa-folder', bool $prepend = false): void
    {
        $extras = ['icon' => '<i class="'.$groupIcon.'"></i>'];

        $subMenu = $this->provider->get('sonata_group_menu', [
            'name' => 'admin.tools',
            'group' => [
                'label' => $groupLabel,
                'on_top' => false,
                'translation_domain' => 'messages',
                'keep_open' => false,
                'items' => $items,
            ],
        ]);

        $subMenu = $menu->addChild($subMenu);
        $subMenu->setExtras(array_merge($subMenu->getExtras(), $extras));

        if ($prepend) {
            $keys = array_map(fn (ItemInterface $item) => $item->getName(), $menu->getChildren());

            usort($keys, fn ($a, $b) => $b === $subMenu->getName());

            $menu->reorderChildren($keys);
        }
    }
}
