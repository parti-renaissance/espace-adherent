<?php

namespace App\Controller\EnMarche;

use App\Entity\Page;
use App\Entity\Proposal;
use App\Repository\ProposalRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProgramController extends AbstractController
{
    /**
     * Redirection to the program.
     *
     * @Route("/programme", methods={"GET"})
     * @Route("/le-programme")
     */
    public function redirectAction(): Response
    {
        return $this->redirectToRoute('program_index', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @Route("/emmanuel-macron/le-programme", name="program_index", methods={"GET"})
     * @Entity("page", expr="repository.findOneBySlug('emmanuel-macron-propositions')")
     */
    public function indexAction(Page $page, ProposalRepository $repository): Response
    {
        return $this->render('program/index.html.twig', [
            'page' => $page,
            'proposals' => $repository->findAllOrderedByPosition(),
        ]);
    }

    /**
     * @Route("/emmanuel-macron/le-programme/{slug}", name="program_proposal", methods={"GET"})
     * @Entity("proposal", expr="repository.findPublishedProposal(slug)")
     */
    public function proposalAction(Proposal $proposal): Response
    {
        return $this->render('program/proposal.html.twig', ['proposal' => $proposal]);
    }
}
