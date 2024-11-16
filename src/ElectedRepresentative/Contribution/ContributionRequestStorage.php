<?php

namespace App\ElectedRepresentative\Contribution;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ContributionRequestStorage
{
    public const SESSION_KEY_COMMAND = 'elected_representative.contribution_request.command';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function save(ContributionRequest $contributionRequest): void
    {
        $this->getSession()->set(self::SESSION_KEY_COMMAND, $contributionRequest);
    }

    public function clear(): void
    {
        $this->getSession()->remove(self::SESSION_KEY_COMMAND);
    }

    public function getContributionRequest(): ContributionRequest
    {
        if (($command = $this->getSession()->get(self::SESSION_KEY_COMMAND)) instanceof ContributionRequest) {
            return $command;
        }

        return new ContributionRequest();
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}
