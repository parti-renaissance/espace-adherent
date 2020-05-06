<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminAdherentController extends Controller
{
    /**
     * Exit impersonation.
     *
     * @Route("/impersonation/exit", name="app_admin_impersonation_exit", methods={"GET"})
     */
    public function exitImpersonationAction(): Response
    {
        $authUtils = $this->get('app.security.authentication_utils');
        $impersonatingUser = $authUtils->getImpersonatingUser();

        if (!$impersonatingUser) {
            return $this->redirectToRoute('homepage');
        }

        $authUtils->authenticateAdmin($impersonatingUser);

        return $this->redirectToRoute('admin_app_adherent_list');
    }
}
