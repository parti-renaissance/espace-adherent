<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\Controller\AccessDelegatorTrait;
use App\Controller\CanaryControllerTrait;
use App\Entity\MyTeam\DelegatedAccess;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-senateur-delegue/{delegated_access_uuid}/messagerie", name="app_message_senator_delegated_")
 *
 * @Security("is_granted('ROLE_DELEGATED_SENATOR') and is_granted('HAS_DELEGATED_ACCESS_MESSAGES', request)")
 */
class DelegatedSenatorMessageController extends SenatorMessageController
{
    use AccessDelegatorTrait;
    use CanaryControllerTrait;

    protected function redirectToMessageRoute(Request $request, string $subName, array $parameters = []): Response
    {
        $delegatedAccess = $this->getDelegatedAccess($request);

        return $this->redirectToRoute("app_message_{$this->getMessageType()}_delegated_${subName}", \array_merge($parameters, [DelegatedAccess::ATTRIBUTE_KEY => $delegatedAccess->getUuid()]));
    }
}
