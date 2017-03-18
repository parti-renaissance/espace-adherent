<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Adherent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/procuration")
 */
class AdminProcurationController extends Controller
{
    /**
     * List the procuration referents invitations URLs.
     *
     * @Route("/referents-invitation-urls", name="app_admin_procuration_referents_invitations_urls")
     * @Method("GET")
     * @Security("has_role('ROLE_TERRITORY')")
     */
    public function referentsInvitationUrlsAction(): Response
    {
        $referents = $this->getDoctrine()->getRepository(Adherent::class)->findReferents();

        return $this->render('admin/procuration_referents_invitation_urls.html.twig', [
            'referents' => $referents,
        ]);
    }
}
