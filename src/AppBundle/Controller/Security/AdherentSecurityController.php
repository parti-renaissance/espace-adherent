<?php

namespace AppBundle\Controller\Security;

use AppBundle\Form\LoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/espace-adherent/connexion")
 */
class AdherentSecurityController extends Controller
{
    /**
     * @Route(name="adherent_login")
     * @Method("GET")
     */
    public function loginAction()
    {
        $securityUtils = $this->get('security.authentication_utils');

        $form = $this->get('form.factory')->createNamed('', LoginType::class, ['_adherent_email' => $securityUtils->getLastUsername()], [
            'username_parameter' => '_adherent_email',
            'password_parameter' => '_adherent_password',
            'csrf_field_name' => '_adherent_csrf',
            'csrf_token_id' => 'authenticate_adherent',
            'action' => $this->generateUrl('adherent_login_check'),
        ]);

        return $this->render('security/adherent_login.html.twig', [
            'form' => $form->createView(),
            'error' => $securityUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/check", name="adherent_login_check")
     * @Method("POST")
     */
    public function loginCheck()
    {
        // No-op
    }
}
