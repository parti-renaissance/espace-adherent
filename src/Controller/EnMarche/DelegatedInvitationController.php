<?php

namespace App\Controller\EnMarche;

use App\Controller\AccessDelegatorTrait;
use App\Controller\CanaryControllerTrait;
use App\Entity\Committee;
use App\Invitation\InvitationRequestHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DelegatedInvitationController extends InvitationController
{
    use AccessDelegatorTrait;
    use CanaryControllerTrait;

    /**
     * @Route("/espace-referent-partage/{delegated_access_uuid}/invitation", name="app_referent_delegated_adherent_invitation", methods={"GET", "POST"}, defaults={"type": "referent"})
     * @Route("/espace-depute-partage/{delegated_access_uuid}/invitation", name="app_deputy_delegated_adherent_invitation", methods={"GET", "POST"}, defaults={"type": "deputy"})
     * @Route("/espace-senateur-partage/{delegated_access_uuid}/invitation", name="app_senator_delegated_adherent_invitation", methods={"GET", "POST"}, defaults={"type": "senator"})
     * @Route("/espace-comite-partage/{delegated_access_uuid}/{slug}/invitation", name="app_supervisor_delegated_adherent_invitation", methods={"GET", "POST"}, defaults={"type": "supervisor"})
     *
     * @Security("is_granted('ROLE_ADHERENT')")
     */
    public function connectedAdherentInviteAction(
        Request $request,
        UserInterface $adherent,
        InvitationRequestHandler $handler,
        string $type,
        ?Committee $committee = null
    ): Response {
        return parent::connectedAdherentInviteAction($request, $this->getMainUser($request), $handler, $type, $committee);
    }
}
