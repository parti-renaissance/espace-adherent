<?php

namespace App\Controller\Renaissance\Election;

use App\Entity\VotingPlatform\Designation\Designation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/election-sas/{uuid}', name: 'app_sas_election')]
#[IsGranted('ROLE_RENAISSANCE_USER')]
class SasElectionController extends AbstractController
{
    #[Route(path: '', name: '_index')]
    public function indexAction(Designation $designation): Response
    {
        return $this->render('renaissance/election/sas.html.twig', ['designation' => $designation]);
    }

    #[Route(path: '/reglement', name: '_regulation')]
    public function regulationAction(Designation $designation): Response
    {
        if (!$designation->wordingRegulationPage) {
            $this->createNotFoundException();
        }

        return $this->render('renaissance/election/regulation.html.twig', ['designation' => $designation]);
    }
}
