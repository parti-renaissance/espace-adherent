<?php

namespace App\OpenAI;

use App\Chatbot\Event\AssistantMessageEvent;
use App\Chatbot\ThreadFactory;
use App\Entity\Chatbot\Message;
use App\Entity\Chatbot\Run;
use App\Entity\Chatbot\Thread;
use App\OpenAI\Client\ClientInterface;
use App\OpenAI\Exception\RunNotInitializedException;
use App\OpenAI\Exception\ThreadNotInitializedException;
use App\Repository\Chatbot\MessageRepository;
use App\Repository\Chatbot\RunRepository;
use App\Repository\Chatbot\ThreadRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OpenAI
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly ThreadFactory $threadFactory,
        private readonly ThreadRepository $threadRepository,
        private readonly MessageRepository $messageRepository,
        private readonly RunRepository $runRepository,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public function cancelCurrentRun(Thread $thread): void
    {
        if (!$run = $thread->currentRun) {
            return;
        }

        $thread->currentRun = null;

        $this->threadRepository->save($thread);

        $this->cancelRun($run);
    }

    public function createRun(Thread $thread, AssistantInterface $assistant): void
    {
        $this->assertThreadInitialized($thread);

        $run = new Run();
        $run->thread = $thread;

        $this->runRepository->save($run);

        $thread->currentRun = $run;

        $this->threadRepository->save($thread);

        $this->initializeRun($run, $assistant);
    }

    public function createUserMessage(Message $message): void
    {
        $thread = $message->thread;

        if (!$thread->openAiId) {
            $this->initializeThread($thread);
        }

        $this->initializeMessage($message);
    }

    public function refreshRun(Thread $thread): void
    {
        if (!$run = $thread->currentRun) {
            return;
        }

        $this->refreshRunStatus($run);

        if (!$run->needRefresh()) {
            $thread->currentRun = null;

            $this->threadRepository->save($thread);
        }
    }

    public function refreshRunStatus(Run $run): void
    {
        $this->assertRunInitialized($run);

        $runStatus = $this->client->getRunStatus(
            $run->thread->openAiId,
            $run->openAiId
        );

        $run->status = $runStatus;

        $this->runRepository->save($run);
    }

    public function retrieveAssistantMessages(Thread $thread): void
    {
        $this->assertThreadInitialized($thread);

        $newMessages = $this->client->getMessages($thread->openAiId);

        foreach ($newMessages as $message) {
            if ($message->isUserMessage()) {
                continue;
            }

            if ($thread->hasMessageWithOpenAiId($message->id)) {
                continue;
            }

            $message = $this->threadFactory->createOpenAIAssistantMessage(
                $thread,
                $message->text,
                $message->annotations,
                $message->date,
                $message->runId ? $this->runRepository->findOneByOpenAiId($message->runId) : null
            );

            $this->messageRepository->save($message);

            $this->dispatcher->dispatch(new AssistantMessageEvent($message));
        }
    }

    private function assertThreadInitialized(Thread $thread): void
    {
        if (!$thread->openAiId) {
            throw new ThreadNotInitializedException();
        }
    }

    private function assertRunInitialized(Run $run): void
    {
        $this->assertThreadInitialized($run->thread);

        if (!$run->openAiId) {
            throw new RunNotInitializedException();
        }
    }

    private function initializeThread(Thread $thread): void
    {
        $thread->openAiId = $this->client->createThread();

        $this->threadRepository->save($thread);
    }

    private function initializeMessage(Message $message): void
    {
        $message->openAiId = $this->client->createUserMessage($message->thread->openAiId, $message->text);

        $this->messageRepository->save($message);
    }

    private function initializeRun(Run $run, AssistantInterface $assistant): void
    {
        $run->openAiId = $this->client->createRun($run->thread->openAiId, $assistant->getIdentifier());

        $this->runRepository->save($run);
    }

    private function cancelRun(Run $run): void
    {
        $thread = $run->thread;

        if (
            $thread->openAiId
            && $run->openAiId
            && $run->isInProgress()
        ) {
            $this->client->cancelRun($thread->openAiId, $run->openAiId);
        }

        $run->cancel();

        $this->runRepository->save($run);
    }
}
