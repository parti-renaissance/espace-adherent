<?php

namespace AppBundle\Controller\Security;

use AppBundle\Form\LoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/espace-adherent")
 */
class AdherentSecurityController extends Controller
{
    /**
     * @Route("/connexion", name="app_adherent_login")
     * @Method("GET")
     */
    public function loginAction(): Response
    {
        $securityUtils = $this->get('security.authentication_utils');

        $form = $this->get('form.factory')->createNamed('', LoginType::class, [
            '_adherent_email' => $securityUtils->getLastUsername(),
        ]);

        return $this->render('security/adherent_login.html.twig', [
            'form' => $form->createView(),
            'error' => $securityUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/connexion", name="app_adherent_login_check")
     * @Method("POST")
     */
    public function loginCheck()
    {
    }

    /**
     * @Route("/deconnexion", name="app_adherent_logout")
     * @Method("GET")
     */
    public function logoutAction()
    {
    }
}
