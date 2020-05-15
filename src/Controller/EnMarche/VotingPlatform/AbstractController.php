<?php

namespace App\Controller\EnMarche\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use App\Repository\VotingPlatform\CandidateGroupRepository;
use App\VotingPlatform\Election\RedirectManager;
use App\VotingPlatform\Election\VoteCommandProcessor;
use App\VotingPlatform\Election\VoteCommandStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends BaseAbstractController
{
    protected $redirectManager;
    protected $storage;
    protected $processor;
    protected $candidateGroupRepository;

    public function __construct(
        RedirectManager $redirectManager,
        VoteCommandStorage $storage,
        VoteCommandProcessor $processor,
        CandidateGroupRepository $candidateGroupRepository
    ) {
        $this->redirectManager = $redirectManager;
        $this->storage = $storage;
        $this->processor = $processor;
        $this->candidateGroupRepository = $candidateGroupRepository;
    }

    protected function redirectToElectionRoute(string $routeName, Election $election): Response
    {
        return $this->redirectToRoute($routeName, ['uuid' => $election->getUuid()]);
    }

    protected function renderElectionTemplate(string $template, Election $election, array $params = []): Response
    {
        return $this->render($template, array_merge($params, [
            'base_layout' => sprintf('voting_platform/_layout_%s.html.twig', $election->getDesignationType()),
            'election' => $election,
        ]));
    }
}
