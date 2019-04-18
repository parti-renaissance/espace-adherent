<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Form\LegislativeCampaignContactMessageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-candidat-legislatives")
 * @Security("is_granted('ROLE_LEGISLATIVE_CANDIDATE')")
 */
class LegislativeCandidateController extends Controller
{
    /**
     * @Route("", name="app_legislative_candidates_platform")
     * @Method("GET")
     */
    public function indexAction(): Response
    {
        return $this->render('legislative_candidate/platform.html.twig');
    }

    /**
     * @Route("/contact", name="app_legislative_candidates_platform_contact")
     * @Method("GET|POST")
     */
    public function contactAction(Request $request): Response
    {
        $form = $this->createForm(LegislativeCampaignContactMessageType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.legislatives.contact_handler')->handle($form->getData());
            $this->addFlash('info', 'legislatives.contact.success');

            return $this->redirectToRoute('app_legislative_candidates_platform_contact');
        }

        return $this->render('legislative_candidate/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
