<?php

namespace App\Chatbot\Handler;

use App\Adherent\Command\RemoveAdherentAndRelatedDataCommand;
use App\Adherent\Unregistration\Handlers\UnregistrationAdherentHandlerInterface;
use App\Chatbot\Command\SynchronizeThreadCommand;
use App\Chatbot\Exception\RunNotCompletedException;
use App\Repository\AdherentRepository;
use App\Repository\Chatbot\ThreadRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SynchronizeThreadCommandHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly ThreadRepository $threadRepository,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function __invoke(SynchronizeThreadCommand $command): void
    {
        $thread = $this->threadRepository->findOneByUuid($command->getUuid()->toString());

        if (!$thread) {
            return;
        }

        // handle thread sync

        if ($thread->currentRun) {
            throw new RunNotCompletedException();
        }
    }
}
