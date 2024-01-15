<?php

namespace App\Chatbot\Handler;

use App\Chatbot\Command\RefreshThreadCommand;
use App\Chatbot\Exception\RunNotCompletedException;
use App\Chatbot\ThreadProcessor;
use App\Repository\Chatbot\ThreadRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RefreshThreadCommandHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly ThreadRepository $threadRepository,
        private readonly ThreadProcessor $threadProcessor,
        LoggerInterface $logger
    ) {
    }

    public function __invoke(RefreshThreadCommand $command): void
    {
        $thread = $this->threadRepository->findOneByUuid($command->getUuid()->toString());

        if (!$thread) {
            $this->logger->error("Did not find thread");
            return;
        }
        $this->logger->warning("Found thread");

        $this->threadRepository->refresh($thread);
        $this->threadProcessor->process($thread);

        if ($thread->currentRun) {
            throw new RunNotCompletedException();
        }
    }
}
