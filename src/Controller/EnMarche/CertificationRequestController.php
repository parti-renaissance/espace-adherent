<?php

namespace App\Controller\EnMarche;

use App\Adherent\Certification\CertificationManager;
use App\Adherent\Certification\CertificationPermissions;
use App\Controller\CanaryControllerTrait;
use App\Entity\Adherent;
use App\Form\CertificationRequestType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-adherent/mon-compte/certification", name="app_certification_request_")
 */
class CertificationRequestController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route(name="home", methods="GET")
     */
    public function homeAction(): Response
    {
        $this->disableInProduction();

        return $this->render('certification_request/home.html.twig');
    }

    /**
     * @Route("/demande", name="form", methods={"GET", "POST"})
     */
    public function requestAction(Request $request, CertificationManager $certificationManager): Response
    {
        $this->disableInProduction();

        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!$this->isGranted(CertificationPermissions::REQUEST)) {
            return $this->redirectToRoute('app_certification_request_home');
        }

        $certificationRequest = $certificationManager->createRequest($adherent);

        $form = $this
            ->createForm(CertificationRequestType::class, $certificationRequest)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $certificationManager->handleRequest($certificationRequest);

            $this->addFlash('info', 'Votre demande de certification a bien été enregistrée.');

            return $this->redirectToRoute('app_certification_request_home');
        }

        return $this->render('certification_request/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
