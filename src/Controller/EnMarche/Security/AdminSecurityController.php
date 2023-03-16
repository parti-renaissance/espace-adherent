<?php

namespace App\Controller\EnMarche\Security;

use App\Entity\Administrator;
use App\Form\LoginType;
use App\Security\QrCodeResponseFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route(path: '/admin')]
class AdminSecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_admin_login', methods: ['GET'])]
    public function loginAction(AuthenticationUtils $securityUtils, FormFactoryInterface $formFactory): Response
    {
        $form = $formFactory->createNamed(
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

    #[Route(path: '/login', name: 'app_admin_login_check', methods: ['POST'])]
    public function loginCheckAction()
    {
    }

    /**
     * QR-code generator to be used with Google Authenticator.
     */
    #[Route(path: '/qr-code/{id}', name: 'app_admin_qr_code', methods: ['GET'])]
    public function qrCodeAction(QrCodeResponseFactory $qrCodeResponseFactory, Administrator $administrator): Response
    {
        return $qrCodeResponseFactory->createResponseFor($administrator);
    }
}
