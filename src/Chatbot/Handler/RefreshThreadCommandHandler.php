<?php

declare(strict_types=1);

namespace App\Chatbot\Handler;

use App\Chatbot\Command\RefreshThreadCommand;
use App\Chatbot\Exception\RunNotCompletedException;
use App\Chatbot\Logger;
use App\Chatbot\ThreadProcessor;
use App\Repository\Chatbot\ThreadRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RefreshThreadCommandHandler
{
    public function __construct(
        private readonly ThreadRepository $threadRepository,
        private readonly ThreadProcessor $threadProcessor,
        private readonly Logger $logger,
    ) {
    }

    public function __invoke(RefreshThreadCommand $command): void
    {
        $thread = $this->threadRepository->findOneByUuid($command->getUuid()->toString());

        if (!$thread) {
            $this->logger->log(\sprintf('Did not find Thread with uuid: "%s"', $command->getUuid()->toString()));

            return;
        }

        $this->threadRepository->refresh($thread);
        $this->threadProcessor->process($thread);

        if ($thread->currentRun) {
            $this->logger->logThread($thread, 'Current run still in progress, retrying');

            throw new RunNotCompletedException();
        }
    }
}
