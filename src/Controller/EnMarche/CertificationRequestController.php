<?php

declare(strict_types=1);

namespace App\Controller\EnMarche;

use App\Adherent\Certification\CertificationManager;
use App\Adherent\Certification\CertificationPermissions;
use App\AppCodeEnum;
use App\Entity\Adherent;
use App\Form\CertificationRequestType;
use App\OAuth\App\AuthAppUrlManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/espace-adherent/mon-compte/certification', name: 'app_certification_request_')]
class CertificationRequestController extends AbstractController
{
    #[Route(name: 'home', methods: 'GET')]
    public function homeAction(Request $request, AuthAppUrlManager $appUrlManager): Response
    {
        $appCode = $appUrlManager->getAppCodeFromRequest($request);
        $isRenaissanceApp = AppCodeEnum::isRenaissanceApp($appCode);

        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!$isRenaissanceApp && $adherent->isRenaissanceUser()) {
            return $this->render('adherent/renaissance_profile.html.twig');
        }

        return $this->render(AppCodeEnum::isRenaissanceApp($appCode)
            ? 'renaissance/adherent/certification_request/home.html.twig'
            : 'certification_request/home.html.twig'
        );
    }

    #[Route(path: '/demande', name: 'form', methods: ['GET', 'POST'])]
    public function requestAction(
        Request $request,
        AuthAppUrlManager $appUrlManager,
        CertificationManager $certificationManager,
        string $app_domain,
    ): Response {
        $appCode = $appUrlManager->getAppCodeFromRequest($request);
        $isRenaissanceApp = AppCodeEnum::isRenaissanceApp($appCode);

        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!$isRenaissanceApp && $adherent->isRenaissanceUser()) {
            return $this->render('adherent/renaissance_profile.html.twig');
        }

        if (!$this->isGranted(CertificationPermissions::REQUEST)) {
            return $this->redirectToRoute('app_certification_request_home', ['app_domain' => $app_domain]);
        }

        $certificationRequest = $certificationManager->createRequest($adherent);

        $form = $this
            ->createForm(CertificationRequestType::class, $certificationRequest)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $certificationManager->handleRequest($certificationRequest);

            $this->addFlash('info', 'Votre demande de certification a bien été enregistrée.');

            return $this->redirectToRoute('app_certification_request_home', ['app_domain' => $app_domain]);
        }

        return $this->render(
            $isRenaissanceApp
                ? 'renaissance/adherent/certification_request/form.html.twig'
                : 'certification_request/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
