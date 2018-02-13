<?php

namespace AppBundle\Admin\EventListener;

use Sonata\AdminBundle\Event\ConfigureMenuEvent;

class AdminEmailMenuListener
{
    public function addMenuItems(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menu['Emails']->addChild('template_list', [
            'label' => 'Templates',
            'route' => 'app_admin_email_template_list',
        ]);
    }
}
