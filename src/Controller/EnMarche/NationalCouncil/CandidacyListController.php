<?php

namespace App\Controller\EnMarche\NationalCouncil;

use App\Repository\Instance\NationalCouncil\CandidaciesGroupRepository;
use App\Repository\Instance\NationalCouncil\ElectionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/conseil-national/candidatures", name="app_national_council_candidacy_list", methods={"GET"})
 *
 * @Security("is_granted('ROLE_NATIONAL_COUNCIL_MEMBER')")
 */
class CandidacyListController extends AbstractController
{
    public function __invoke(
        ElectionRepository $electionRepository,
        CandidaciesGroupRepository $candidaciesGroupRepository
    ): Response {
        $election = $electionRepository->findLast();

        return $this->render('national_council/candidacy_list.html.twig', [
            'election' => $election,
            'candidacies_groups' => $candidaciesGroupRepository->findForElection($election),
        ]);
    }
}
