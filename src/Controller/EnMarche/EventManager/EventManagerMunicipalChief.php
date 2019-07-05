<?php

namespace AppBundle\Controller\EnMarche\EventManager;

use AppBundle\Event\EventManagerSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-chef-municipal", name="app_municipal_chief_event_manager_")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class EventManagerMunicipalChief extends AbstractEventManagerController
{
    protected function getSpaceType(): string
    {
        return EventManagerSpaceEnum::MUNICIPAL_CHIEF;
    }
}
