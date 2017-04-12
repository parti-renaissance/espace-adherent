<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\TonMacronChoice;
use AppBundle\Entity\TonMacronFriendInvitation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/tonmacron")
 */
class AdminTonMacronController extends Controller
{
    /**
     * @Route("/export/choices", name="app_admin_tonmacron_export_choices")
     * @Method("GET")
     * @Security("has_role('ROLE_TERRITORY')")
     */
    public function exportChoicesAction(): Response
    {
        $choices = $this->getDoctrine()->getRepository(TonMacronChoice::class)->findAll();
        $exported = $this->get('app.ton_macron.serializer')->serializeChoices($choices);

        return new Response($exported, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="tonmacron-choices.csv"',
        ]);
    }

    /**
     * @Route("/export/invitations", name="app_admin_tonmacron_export_invitations")
     * @Method("GET")
     * @Security("has_role('ROLE_TERRITORY')")
     */
    public function exportInvitationsAction(): Response
    {
        $invitations = $this->getDoctrine()->getRepository(TonMacronFriendInvitation::class)->findAll();
        $exported = $this->get('app.ton_macron.serializer')->serializeInvitations($invitations);

        return new Response($exported, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="tonmacron-invitations.csv"',
        ]);
    }
}
