<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Security\AuthenticationUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AdminAdherentController extends Controller
{
    /**
     * Exit impersonation.
     *
     * @Route("/impersonation/exit", name="app_admin_impersonation_exit")
     * @Method("GET")
     */
    public function exitImpersonationAction(AuthenticationUtils $authUtils): Response
    {
        $impersonatingUser = $authUtils->getImpersonatingUser();

        if (!$impersonatingUser) {
            return $this->redirectToRoute('homepage');
        }

        $authUtils->authenticateAdmin($impersonatingUser);

        return $this->redirectToRoute('admin_app_adherent_list');
    }
}
