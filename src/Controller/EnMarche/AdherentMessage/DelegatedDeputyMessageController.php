<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\Controller\AccessDelegatorTrait;
use App\Controller\CanaryControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-depute-delegue/messagerie", name="app_message_deputy_delegated_")
 *
 * @Security("is_granted('IS_DELEGATED_DEPUTY') and is_granted('HAS_DELEGATED_ACCESS_MESSAGES', 'deputy')")
 */
class DelegatedDeputyMessageController extends DeputyMessageController
{
    use AccessDelegatorTrait;
    use CanaryControllerTrait;

    protected function redirectToMessageRoute(string $subName, array $parameters = []): Response
    {
        return $this->redirectToRoute("app_message_{$this->getMessageType()}_delegated_${subName}", $parameters);
    }
}
