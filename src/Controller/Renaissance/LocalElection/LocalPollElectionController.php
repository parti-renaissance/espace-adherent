<?php

namespace App\Controller\Renaissance\LocalElection;

use App\Entity\Adherent;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\ElectionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/election-locale', name: 'app_renaissance_local_election_home', methods: 'GET')]
#[IsGranted('RENAISSANCE_ADHERENT')]
class LocalPollElectionController extends AbstractController
{
    public function __invoke(ElectionManager $electionManager): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!$designations = $electionManager->findActiveDesignations($adherent, [DesignationTypeEnum::LOCAL_POLL])) {
            return $this->redirectToRoute('app_renaissance_homepage');
        }

        return $this->render('renaissance/local_election/local_poll_index.html.twig', [
            'designation' => current($designations),
        ]);
    }
}
