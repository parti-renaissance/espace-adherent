<?php

namespace App\Procuration;

use App\Entity\ProcurationRequest;
use App\Procuration\Exception\InvalidProcurationFlowException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ProcurationSession
{
    private const PROCURATION_KEY = 'app_procuration_model';
    private const ELECTION_CONTEXT_KEY = 'app_procuration_election_context';

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function startRequest(): void
    {
        if (!$this->hasElectionContext()) {
            throw new InvalidProcurationFlowException('An election context is required to start the flow.');
        }

        $this->session->set(self::PROCURATION_KEY, new ProcurationRequest());
    }

    public function endRequest(): void
    {
        $this->session->remove(self::PROCURATION_KEY);
        $this->session->remove(self::ELECTION_CONTEXT_KEY);
    }

    public function getCurrentRequest(): ProcurationRequest
    {
        if (!$this->session->has(self::PROCURATION_KEY)) {
            $this->startRequest();
        }

        return $this->session->get(self::PROCURATION_KEY);
    }

    public function hasElectionContext(): bool
    {
        return $this->session->has(self::ELECTION_CONTEXT_KEY);
    }

    public function getElectionContext(): ElectionContext
    {
        if (!$this->hasElectionContext()) {
            throw new InvalidProcurationFlowException('No election context.');
        }

        return \unserialize($this->session->get(self::ELECTION_CONTEXT_KEY));
    }

    public function setElectionContext(ElectionContext $context)
    {
        // Context has changed, reset
        $this->endRequest();
        $this->session->set(self::ELECTION_CONTEXT_KEY, \serialize($context));
        $this->startRequest();
    }
}
