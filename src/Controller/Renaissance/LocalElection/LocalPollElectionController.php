<?php

namespace App\Controller\Renaissance\LocalElection;

use App\Entity\Adherent;
use App\Repository\VotingPlatform\DesignationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/election-locale", name="app_renaissance_local_election_home", methods="GET")
 * @IsGranted("ROLE_ADHERENT")
 */
class LocalPollElectionController extends AbstractController
{
    public function __invoke(DesignationRepository $designationRepository): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!$designation = $designationRepository->findFirstActiveForZones($adherent->getParentZones())) {
            return $this->redirectToRoute('app_renaissance_homepage');
        }

        return $this->render('renaissance/local_election/local_poll_index.html.twig', [
            'designation' => $designation,
        ]);
    }
}
