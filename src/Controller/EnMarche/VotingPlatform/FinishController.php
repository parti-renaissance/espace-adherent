<?php

namespace App\Controller\EnMarche\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/fin", name="app_voting_platform_finish_step", methods={"GET"})
 */
class FinishController extends AbstractController
{
    public function __invoke(Election $election): Response
    {
        return $this->renderElectionTemplate(
            'voting_platform/finish.html.twig',
            $election,
            ['voter_key' => $this->storage->getLastVoterKey()]
        );
    }
}
