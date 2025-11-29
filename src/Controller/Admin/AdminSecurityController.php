<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Administrator;
use App\Form\LoginType;
use App\Security\QrCodeResponseFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminSecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_admin_login', methods: ['GET', 'POST'])]
    public function loginAction(AuthenticationUtils $securityUtils): Response
    {
        $form = $this->createForm(LoginType::class, ['_username' => $securityUtils->getLastUsername()]);

        return $this->render('security/admin_login.html.twig', [
            'form' => $form->createView(),
            'error' => $securityUtils->getLastAuthenticationError(),
        ]);
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
