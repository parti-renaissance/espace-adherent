<?php

declare(strict_types=1);

namespace App\Chatbot;

use App\Chatbot\Exception\RunNotSynchronisedException;
use App\Chatbot\Exception\ThreadNotSynchronisedException;
use App\Chatbot\Provider\ProviderInterface;
use App\Chatbot\Provider\Resources\Message as ResourceMessage;
use App\Entity\Chatbot\Message;
use App\Entity\Chatbot\Run;
use App\Entity\Chatbot\Thread;

class Client
{
    public function __construct(private readonly ProviderInterface $provider)
    {
    }

    public function createThread(): string
    {
        return $this->provider->createThread();
    }

    public function createMessage(Message $message): string
    {
        $thread = $message->thread;

        self::assertInitializedThread($thread);

        return $this->provider->createMessage($thread->externalId, $message->role, $message->content);
    }

    public function createRun(Run $run): string
    {
        $thread = $run->thread;

        self::assertInitializedThread($thread);

        return $this->provider->createRun($thread->externalId, $thread->chatbot->assistantId);
    }

    public function cancelRun(Run $run): void
    {
        self::assertInitializedRun($run);

        $this->provider->cancelRun($run->thread->externalId, $run->externalId);
    }

    public function getRunStatus(Run $run): string
    {
        self::assertInitializedRun($run);

        return $this->provider->getRunStatus($run->thread->externalId, $run->externalId);
    }

    /**
     * @return ResourceMessage[]|array
     */
    public function getLastMessages(Thread $thread, int $limit = 10): array
    {
        self::assertInitializedThread($thread);

        return $this->provider->retrieveMessages($thread->externalId);
    }

    private static function assertInitializedThread(Thread $thread): void
    {
        if (!$thread->isInitialized()) {
            throw new ThreadNotSynchronisedException();
        }
    }

    private static function assertInitializedRun(Run $run): void
    {
        self::assertInitializedThread($run->thread);

        if (!$run->isInitialized()) {
            throw new RunNotSynchronisedException();
        }
    }
}
