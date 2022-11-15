<?php

namespace App\Controller\EnMarche;

use App\Adherent\Certification\CertificationManager;
use App\Adherent\Certification\CertificationPermissions;
use App\AppCodeEnum;
use App\Entity\Adherent;
use App\Form\CertificationRequestType;
use App\OAuth\App\AuthAppUrlManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-adherent/mon-compte/certification", name="app_certification_request_")
 * @IsGranted("ADHERENT_PROFILE")
 */
class CertificationRequestController extends AbstractController
{
    /**
     * @Route(name="home", methods="GET")
     */
    public function homeAction(Request $request, AuthAppUrlManager $appUrlManager): Response
    {
        $appCode = $appUrlManager->getAppCodeFromRequest($request);

        return $this->render(AppCodeEnum::isRenaissanceApp($appCode)
            ? 'renaissance/adherent/certification_request/home.html.twig'
            : 'certification_request/home.html.twig'
        );
    }

    /**
     * @Route("/demande", name="form", methods={"GET", "POST"})
     */
    public function requestAction(
        Request $request,
        AuthAppUrlManager $appUrlManager,
        CertificationManager $certificationManager,
        string $app_domain
    ): Response {
        $appCode = $appUrlManager->getAppCodeFromRequest($request);

        /** @var Adherent $adherent */
        $adherent = $this->getUser();

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
            AppCodeEnum::isRenaissanceApp($appCode)
                ? 'renaissance/adherent/certification_request/form.html.twig'
                : 'certification_request/form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
