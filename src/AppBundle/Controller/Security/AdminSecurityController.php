<?php

namespace AppBundle\Controller\Security;

use AppBundle\Entity\Administrator;
use AppBundle\Form\LoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class AdminSecurityController extends Controller
{
    /**
     * @Route("/login", name="app_admin_login")
     * @Method("GET")
     */
    public function loginAction(): Response
    {
        $securityUtils = $this->get('security.authentication_utils');

        $form = $this->get('form.factory')->createNamed(
            '',
            LoginType::class,
            [
                '_admin_email' => $securityUtils->getLastUsername(),
            ],
            [
                'username_parameter' => '_admin_email',
                'password_parameter' => '_admin_password',
                'csrf_field_name' => '_admin_csrf',
                'csrf_token_id' => 'authenticate_admin',
            ]
        );

        return $this->render('security/admin_login.html.twig', [
            'form' => $form->createView(),
            'error' => $securityUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/login", name="app_admin_login_check")
     * @Method("POST")
     */
    public function loginCheck()
    {
    }

    /**
     * @Route("/logout", name="app_admin_logout")
     * @Method("GET")
     */
    public function logoutAction()
    {
    }

    /**
     * QR-code generator to be used with Google Authenticator.
     *
     * @Route("/qr-code/{id}", name="app_admin_qr_code")
     * @Method("GET")
     */
    public function qrCodeAction(Administrator $administrator): Response
    {
        return $this->get('app.security.2fa_qr_code_factory')->createQrCodeResponse($administrator);
    }
}
