<?php

namespace App\Adherent\Contribution;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ContributionRequestStorage
{
    public const SESSION_KEY_COMMAND = 'contribution_request.command';

    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function save(ContributionRequest $contributionRequest): void
    {
        $this->session->set(self::SESSION_KEY_COMMAND, $contributionRequest);
    }

    public function clear(): void
    {
        $this->session->remove(self::SESSION_KEY_COMMAND);
    }

    public function getContributionRequest(): ContributionRequest
    {
        if (($command = $this->session->get(self::SESSION_KEY_COMMAND)) instanceof ContributionRequest) {
            return $command;
        }

        return new ContributionRequest();
    }
}
