<?php

namespace AppBundle\Controller;

use AppBundle\Form\LegislativeCampaignContactMessageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        return $this->render('legislatives/platform.html.twig');
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
            $this->addFlash('info', $this->get('translator')->trans('legislatives.contact.success'));

            return $this->redirectToRoute('app_legislative_candidates_platform_contact');
        }

        return $this->render('legislatives/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
