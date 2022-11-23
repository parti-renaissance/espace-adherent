<?php

namespace App\Controller\EnMarche\VotingPlatform;

use App\AppCodeEnum;
use App\Entity\VotingPlatform\Election;
use App\OAuth\App\AuthAppUrlManager;
use App\Repository\VotingPlatform\CandidateGroupRepository;
use App\VotingPlatform\Election\RedirectManager;
use App\VotingPlatform\Election\VoteCommandProcessor;
use App\VotingPlatform\Election\VoteCommandStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends BaseAbstractController
{
    protected RedirectManager $redirectManager;
    protected VoteCommandStorage $storage;
    protected VoteCommandProcessor $processor;
    protected CandidateGroupRepository $candidateGroupRepository;
    private AuthAppUrlManager $appUrlManager;
    private RequestStack $requestStack;

    public function __construct(
        RedirectManager $redirectManager,
        VoteCommandStorage $storage,
        VoteCommandProcessor $processor,
        CandidateGroupRepository $candidateGroupRepository,
        AuthAppUrlManager $appUrlManager,
        RequestStack $requestStack
    ) {
        $this->redirectManager = $redirectManager;
        $this->storage = $storage;
        $this->processor = $processor;
        $this->candidateGroupRepository = $candidateGroupRepository;
        $this->appUrlManager = $appUrlManager;
        $this->requestStack = $requestStack;
    }

    protected function redirectToElectionRoute(string $routeName, Election $election): Response
    {
        return $this->redirectToRoute($routeName, ['uuid' => $election->getUuid()]);
    }

    protected function renderElectionTemplate(string $template, Election $election, array $params = []): Response
    {
        $appCode = $this->appUrlManager->getAppCodeFromRequest($this->requestStack->getMasterRequest());
        $isRenaissanceApp = AppCodeEnum::isRenaissanceApp($appCode);

        return $this->render($isRenaissanceApp ? 'renaissance/'.$template : $template, array_merge($params, [
            'base_layout' => sprintf('%svoting_platform/_layout_%s.html.twig', $isRenaissanceApp ? 'renaissance/' : '', $designationType = $election->getDesignationType()),
            'designation_type' => $designationType,
            'election' => $election,
        ]));
    }
}
