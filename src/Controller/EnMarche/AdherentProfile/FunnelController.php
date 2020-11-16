<?php

namespace App\Controller\EnMarche\AdherentProfile;

use App\AdherentProfile\AdherentProfile;
use App\AdherentProfile\AdherentProfileHandler;
use App\Controller\EnMarche\VotingPlatform\AbstractController;
use App\Form\AdherentFunnelGeneralType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FunnelController extends AbstractController
{
    /**
     * @Route("/funnel/general", name="app_funnel_general", methods={"GET", "POST"})
     */
    public function generalAction(Request $request, AdherentProfileHandler $handler): Response
    {
        $adherent = $this->getUser();
        $adherentProfile = AdherentProfile::createFromAdherent($adherent);

        $form = $this->createForm(AdherentFunnelGeneralType::class, $adherentProfile);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $handler->update($adherent, $adherentProfile);
            $this->addFlash('info', 'adherent.update_profile.success');

            return $this->redirectToRoute('app_funnel_certification');
        }

        return $this->render('adherent_profile/funnel/general.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/funnel/certification", name="app_funnel_certification", methods={"GET", "POST"})
     */
    public function certificationAction(): Response
    {
        return $this->render('certification_request/home.html.twig', [
            'funnel' => true,
        ]);
    }
}
