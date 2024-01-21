<?php

namespace App\OpenAI\Handler;

use App\OpenAI\Command\RunThreadCommand;
use App\OpenAI\Exception\RunNeedRefreshException;
use App\OpenAI\Logger;
use App\OpenAI\OpenAI;
use App\Repository\Chatbot\ThreadRepository;
use App\Repository\OpenAI\AssistantRepository;

class RunThreadCommandHandler
{
    public function __construct(
        public readonly ThreadRepository $threadRepository,
        public readonly AssistantRepository $assistantRepository,
        public readonly OpenAI $openAI,
        public readonly Logger $logger
    ) {
    }

    public function __invoke(RunThreadCommand $command): void
    {
        $thread = $this->threadRepository->findOneByUuid($command->threadIdentifier);

        if (!$thread) {
            return;
        }

        $assistant = $this->assistantRepository->loadByIdentifier($command->assistantIdentifier);

        if (!$assistant) {
            return;
        }

        $this->threadRepository->refresh($thread);

        $this->logger->log($thread, 'Starting handler.');

        $newMessages = $thread->getMessagesToInitializeOnOpenAi();

        $this->logger->log($thread, sprintf('Found %d new messages.', $newMessages->count()));

        if (!$newMessages->isEmpty()) {
            if ($thread->hasCurrentRun()) {
                $this->logger->log($thread, 'Cancelling current run.');

                $this->openAI->cancelCurrentRun($thread);
            }

            $this->logger->log($thread, 'Initializing new messages.');

            foreach ($newMessages as $message) {
                $this->openAI->createUserMessage($message);
            }

            $this->logger->log($thread, 'Creating new run.');

            $this->openAI->createRun($thread, $assistant);

            $this->logger->log($thread, 'New run created; retrying command.');

            throw new RunNeedRefreshException();
        }

        if (!$currentRun = $thread->currentRun) {
            $this->logger->log($thread, 'No current run; dropping.');

            return;
        }

        $this->logger->log($thread, 'Refreshing current run.');

        $this->openAI->refreshRun($thread);

        if ($currentRun->needRefresh()) {
            $this->logger->log($thread, 'Current run still in progress; retrying command.');

            throw new RunNeedRefreshException();
        }

        if ($currentRun->isCompleted()) {
            $this->logger->log($thread, 'Run completed; fetching new assistant messages.');

            $this->openAI->retrieveAssistantMessages($thread);
        }

        $this->logger->log($thread, 'Ending handler');
    }
}
