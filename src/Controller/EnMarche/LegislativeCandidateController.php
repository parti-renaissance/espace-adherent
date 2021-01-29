<?php

namespace App\Controller\EnMarche;

use App\Form\LegislativeCampaignContactMessageType;
use App\Legislative\LegislativeCampaignContactMessageHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-candidat-legislatives")
 * @Security("is_granted('ROLE_LEGISLATIVE_CANDIDATE')")
 */
class LegislativeCandidateController extends AbstractController
{
    /**
     * @Route("", name="app_legislative_candidates_platform", methods={"GET"})
     */
    public function indexAction(): Response
    {
        return $this->render('legislative_candidate/platform.html.twig');
    }

    /**
     * @Route("/contact", name="app_legislative_candidates_platform_contact", methods={"GET", "POST"})
     */
    public function contactAction(Request $request, LegislativeCampaignContactMessageHandler $handler): Response
    {
        $form = $this->createForm(LegislativeCampaignContactMessageType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->handle($form->getData());
            $this->addFlash('info', 'legislatives.contact.success');

            return $this->redirectToRoute('app_legislative_candidates_platform_contact');
        }

        return $this->render('legislative_candidate/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
