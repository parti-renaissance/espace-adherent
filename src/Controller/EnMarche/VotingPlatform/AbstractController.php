<?php

declare(strict_types=1);

namespace App\Controller\EnMarche\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use App\Repository\VotingPlatform\CandidateGroupRepository;
use App\VotingPlatform\Election\VoteCommandProcessor;
use App\VotingPlatform\Election\VoteCommandStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

abstract class AbstractController extends BaseAbstractController
{
    public function __construct(
        protected readonly VoteCommandStorage $storage,
        protected readonly VoteCommandProcessor $processor,
        protected readonly CandidateGroupRepository $candidateGroupRepository,
        private readonly Environment $twig,
    ) {
    }

    protected function redirectToElectionRoute(string $routeName, Election $election): Response
    {
        return $this->redirectToRoute($routeName, ['uuid' => $election->getUuid()]);
    }

    protected function renderElectionTemplate(
        string $template,
        Election $election,
        array $params = [],
    ): Response {
        $baseTemplate = \sprintf('voting_platform/_layout_%s.html.twig', $designationType = $election->getDesignationType());

        if (!$this->twig->getLoader()->exists($baseTemplate)) {
            $baseTemplate = 'voting_platform/_layout_base.html.twig';
        }

        return $this->render($template, array_merge($params, [
            'base_layout' => $baseTemplate,
            'designation_type' => $designationType,
            'election' => $election,
        ]));
    }
}
