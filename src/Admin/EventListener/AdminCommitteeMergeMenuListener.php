<?php

namespace AppBundle\Admin\EventListener;

use Sonata\AdminBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AdminCommitteeMergeMenuListener
{
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function addMenuItems(ConfigureMenuEvent $event): void
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN_COMMITTEES_MERGE')) {
            return;
        }

        $menu = $event->getMenu();

        $menu['Système']->addChild('committee_merge', [
            'label' => 'Fusion de comités',
            'route' => 'app_admin_committee_merge',
        ]);
    }
}
