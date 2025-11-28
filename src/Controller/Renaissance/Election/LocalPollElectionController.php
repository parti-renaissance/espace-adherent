<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Election;

use App\Entity\VotingPlatform\Designation\Designation;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\ElectionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route(path: '/election-locale', name: 'app_renaissance_local_election_home', methods: 'GET')]
class LocalPollElectionController extends AbstractController
{
    public function __invoke(Request $request, ElectionManager $electionManager): Response
    {
        $designations = $electionManager->findActiveDesignations($this->getUser(), [DesignationTypeEnum::LOCAL_POLL]);

        if ($designationUuid = $request->query->get('uuid')) {
            $designations = array_filter($designations, fn (Designation $designation) => $designation->getUuid()->toString() === $designationUuid);
        }

        if (!$designations) {
            return $this->redirectToRoute('vox_app_redirect');
        }

        return $this->render('renaissance/local_election/local_poll_index.html.twig', [
            'designation' => current($designations),
        ]);
    }
}
