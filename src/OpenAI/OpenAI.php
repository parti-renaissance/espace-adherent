<?php

namespace App\OpenAI;

use App\OpenAI\Client\ClientInterface;
use App\OpenAI\Event\AssistantMessageEvent;
use App\OpenAI\Exception\RunNotInitializedException;
use App\OpenAI\Exception\ThreadNotInitializedException;
use App\OpenAI\Model\AssistantInterface;
use App\OpenAI\Model\MessageInterface;
use App\OpenAI\Model\RunInterface;
use App\OpenAI\Model\ThreadInterface;
use App\OpenAI\Provider\MessageProviderInterface;
use App\OpenAI\Provider\RunProviderInterface;
use App\OpenAI\Provider\ThreadProviderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OpenAI
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly ThreadProviderInterface $threadProvider,
        private readonly MessageProviderInterface $messageProvider,
        private readonly RunProviderInterface $runProvider,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public function cancelCurrentRun(ThreadInterface $thread): void
    {
        if (!$run = $thread->getCurrentRun()) {
            return;
        }

        $thread->removeCurrentRun();

        $this->threadProvider->save($thread);

        $this->cancelRun($run);
    }

    public function createRun(ThreadInterface $thread, AssistantInterface $assistant): void
    {
        $this->assertThreadInitialized($thread);

        $thread->createCurrentRun();

        $this->threadProvider->save($thread);

        $this->initializeRun($thread->getCurrentRun(), $assistant);
    }

    public function createUserMessage(MessageInterface $message): void
    {
        $thread = $message->getThread();

        if (!$thread->hasOpenAiId()) {
            $this->initializeThread($thread);
        }

        $this->initializeMessage($message);
    }

    public function refreshRun(ThreadInterface $thread): void
    {
        if (!$run = $thread->getCurrentRun()) {
            return;
        }

        $this->refreshRunStatus($run);

        if (!$run->needRefresh()) {
            $thread->removeCurrentRun();

            $this->threadProvider->save($thread);
        }
    }

    public function refreshRunStatus(RunInterface $run): void
    {
        $this->assertRunInitialized($run);

        $openAiRunStatus = $this->client->getRunStatus(
            $run->thread->openAiId,
            $run->openAiId
        );

        $run->updateOpenAiStatus($openAiRunStatus);

        $this->runProvider->save($run);
    }

    public function retrieveAssistantMessages(ThreadInterface $thread): void
    {
        $this->assertThreadInitialized($thread);

        $newMessages = $this->client->getMessages($thread->getOpenAiId());

        foreach ($newMessages as $messageResponse) {
            if ($messageResponse->isUserMessage()) {
                continue;
            }

            if ($thread->hasMessageWithOpenAiId($messageResponse->id)) {
                continue;
            }

            $message = $this->messageProvider->createAssistantMessage(
                $thread,
                $messageResponse->id,
                $messageResponse->text,
                $messageResponse->annotations,
                $messageResponse->date,
                $messageResponse->runId ? $this->runProvider->findOneByOpenAiId($messageResponse->runId) : null
            );

            $this->messageProvider->save($message);

            $this->dispatcher->dispatch(new AssistantMessageEvent($message));
        }
    }

    private function assertThreadInitialized(ThreadInterface $thread): void
    {
        if (!$thread->hasOpenAiId()) {
            throw new ThreadNotInitializedException();
        }
    }

    private function assertRunInitialized(RunInterface $run): void
    {
        $this->assertThreadInitialized($run->getThread());

        if (!$run->hasOpenAiId()) {
            throw new RunNotInitializedException();
        }
    }

    private function initializeThread(ThreadInterface $thread): void
    {
        $thread->setOpenAiId($this->client->createThread());

        $this->threadProvider->save($thread);
    }

    private function initializeMessage(MessageInterface $message): void
    {
        $message->openAiId = $this->client->createUserMessage(
            $message->getThread()->getOpenAiId(),
            $message->getText()
        );

        $this->messageProvider->save($message);
    }

    private function initializeRun(RunInterface $run, AssistantInterface $assistant): void
    {
        $run->openAiId = $this->client->createRun($run->getThread()->getOpenAiId(), $assistant->getOpenAiId());

        $this->runProvider->save($run);
    }

    private function cancelRun(RunInterface $run): void
    {
        $thread = $run->getThread();

        if (
            $thread->hasOpenAiId()
            && $run->hasOpenAiId()
            && $run->isInProgress()
        ) {
            $this->client->cancelRun($thread->getOpenAiId(), $run->getOpenAiId());
        }

        $run->cancel();

        $this->runProvider->save($run);
    }
}
