<?php

namespace App\Controller\EnMarche\Jecoute;

use App\Controller\AccessDelegatorTrait;
use App\Controller\CanaryControllerTrait;
use App\Entity\MyTeam\DelegatedAccess;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent-partage/{delegated_access_uuid}/jecoute", name="app_jecoute_referent_delegated_")
 *
 * @Security("is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_JECOUTE', request)")
 */
class DelegatedJecouteReferentController extends JecouteReferentController
{
    use AccessDelegatorTrait;
    use CanaryControllerTrait;

    protected function redirectToJecouteRoute(Request $request, string $subName, array $parameters = []): Response
    {
        $delegatedAccess = $this->getDelegatedAccess($request);

        return $this->redirectToRoute("app_jecoute_{$this->getSpaceName()}_delegated_${subName}", array_merge([DelegatedAccess::ATTRIBUTE_KEY => $delegatedAccess->getUuid()], $parameters));
    }

    protected function getSurveyTags(Request $request): array
    {
        return $this->getMainUser($request)->getManagedAreaTagCodes();
    }
}
