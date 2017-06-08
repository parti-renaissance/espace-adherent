<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Page;
use AppBundle\Entity\Proposal;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ProgramController extends AbstractController
{
    /**
     * Redirection to the program.
     *
     * @Route("/programme", defaults={"_enable_campaign_silence"=true})
     * @Route("/le-programme", defaults={"_enable_campaign_silence"=true})
     * @Method("GET")
     */
    public function redirectAction()
    {
        return $this->redirectToRoute('program_index', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @Route("/emmanuel-macron/le-programme", defaults={"_enable_campaign_silence"=true}, name="program_index")
     * @Method("GET")
     * @Entity("page", expr="repository.findOneBySlug('emmanuel-macron-propositions')")
     */
    public function indexAction(Page $page)
    {
        return $this->render('program/index.html.twig', [
            'page' => $page,
            'proposals' => $this->getDoctrine()->getRepository(Proposal::class)->findAllOrderedByPosition(),
        ]);
    }

    /**
     * @Route("/emmanuel-macron/le-programme/{slug}", defaults={"_enable_campaign_silence"=true}, name="program_proposal")
     * @Method("GET")
     * @Entity("proposal", expr="repository.findPublishedProposal(slug)")
     */
    public function proposalAction(Proposal $proposal)
    {
        return $this->render('program/proposal.html.twig', ['proposal' => $proposal]);
    }
}
