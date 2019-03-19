<?php

namespace AppBundle\Controller\EnMarche\Security;

use AppBundle\Entity\Administrator;
use AppBundle\Form\LoginType;
use AppBundle\Security\QrCodeResponseFactory;
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
                '_login_email' => $securityUtils->getLastUsername(),
            ],
            [
                'username_parameter' => '_login_email',
                'password_parameter' => '_login_password',
                'csrf_field_name' => '_login_csrf',
                'csrf_token_id' => 'authenticate',
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
    public function loginCheckAction()
    {
    }

    /**
     * QR-code generator to be used with Google Authenticator.
     *
     * @Route("/qr-code/{id}", name="app_admin_qr_code")
     * @Method("GET")
     */
    public function qrCodeAction(QrCodeResponseFactory $qrCodeResponseFactory, Administrator $administrator): Response
    {
        return $qrCodeResponseFactory->createResponseFor($administrator);
    }
}
