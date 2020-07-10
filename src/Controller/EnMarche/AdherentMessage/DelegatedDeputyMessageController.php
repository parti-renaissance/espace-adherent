<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\Controller\AccessDelegatorTrait;
use App\Entity\MyTeam\DelegatedAccess;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-depute-partage/{delegated_access_uuid}/messagerie", name="app_message_deputy_delegated_")
 *
 * @Security("is_granted('ROLE_DELEGATED_DEPUTY') and is_granted('HAS_DELEGATED_ACCESS_MESSAGES', request)")
 */
class DelegatedDeputyMessageController extends DeputyMessageController
{
    use AccessDelegatorTrait;

    protected function redirectToMessageRoute(Request $request, string $subName, array $parameters = []): Response
    {
        $delegatedAccess = $this->getDelegatedAccess($request);

        return $this->redirectToRoute("app_message_{$this->getMessageType()}_delegated_${subName}", \array_merge($parameters, [DelegatedAccess::ATTRIBUTE_KEY => $delegatedAccess->getUuid()]));
    }
}
