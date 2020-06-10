<?php

namespace App\Controller\EnMarche\EventManager;

use App\Controller\AccessDelegatorTrait;
use App\Controller\CanaryControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-depute-delegue", name="app_deputy_event_manager_delegated_")
 *
 * @Security("is_granted('IS_DELEGATED_DEPUTY') and is_granted('HAS_DELEGATED_ACCESS_EVENTS', 'deputy')")
 */
class DelegatedDeputyEventManagerController extends DeputyEventManagerController
{
    use AccessDelegatorTrait;
    use CanaryControllerTrait;

    protected function redirectToJecouteRoute(string $subName, array $parameters = []): Response
    {
        return $this->redirectToRoute("app_event_manager_{$this->getSpaceType()}_delegated_${subName}", $parameters);
    }
}
