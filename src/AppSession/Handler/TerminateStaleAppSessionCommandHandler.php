<?php

namespace App\AppSession\Handler;

use App\AppSession\Command\TerminateStaleAppSessionCommand;
use App\Repository\AppSessionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TerminateStaleAppSessionCommandHandler
{
    public function __construct(private readonly AppSessionRepository $appSessionRepository)
    {
    }

    public function __invoke(TerminateStaleAppSessionCommand $command): void
    {
        $this->appSessionRepository->terminateStaleSessions();
    }
}
