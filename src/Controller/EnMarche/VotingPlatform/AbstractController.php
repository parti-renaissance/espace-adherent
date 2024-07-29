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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

abstract class AbstractController extends BaseAbstractController
{
    public function __construct(
        protected readonly RedirectManager $redirectManager,
        protected readonly VoteCommandStorage $storage,
        protected readonly VoteCommandProcessor $processor,
        protected readonly CandidateGroupRepository $candidateGroupRepository,
        private readonly AuthAppUrlManager $appUrlManager,
        private readonly Environment $twig
    ) {
    }

    protected function redirectToElectionRoute(string $routeName, Election $election): Response
    {
        return $this->redirectToRoute($routeName, ['uuid' => $election->getUuid()]);
    }

    protected function renderElectionTemplate(
        string $template,
        Election $election,
        Request $request,
        array $params = []
    ): Response {
        $appCode = $this->appUrlManager->getAppCodeFromRequest($request);

        $baseTemplate = \sprintf('voting_platform/_layout_%s.html.twig', $designationType = $election->getDesignationType());

        if (!$this->twig->getLoader()->exists($baseTemplate)) {
            $baseTemplate = 'voting_platform/_layout_base.html.twig';
        }

        return $this->render($template, array_merge($params, [
            'base_layout' => $baseTemplate,
            'designation_type' => $designationType,
            'election' => $election,
            'is_renaissance_app' => AppCodeEnum::isRenaissanceApp($appCode),
        ]));
    }
}
