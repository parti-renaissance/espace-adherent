<?php

namespace App\Controller\EnMarche\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'app_voting_platform_index', methods: ['GET'])]
class IndexController extends AbstractController
{
    public function __invoke(Election $election): Response
    {
        $voteCommand = $this->storage->getVoteCommand($election);

        if (!$this->processor->canStart($voteCommand)) {
            return $this->redirectToRoute('vox_app');
        }

        $this->processor->doStart($voteCommand);

        return $this->renderElectionTemplate('voting_platform/index.html.twig', $election);
    }
}
