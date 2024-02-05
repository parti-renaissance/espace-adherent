<?php

namespace App\OpenAI\EventListener;

use App\OpenAI\Command\RunThreadCommand;
use App\OpenAI\Event\ThreadEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEventListener]
class ThreadListener
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function __invoke(ThreadEvent $event): void
    {
        $thread = $event->thread;
        $assistant = $event->assistant;

        $this->bus->dispatch(
            new RunThreadCommand(
                $thread->getUuid()->toString(),
                $assistant->getIdentifier()
            )
        );
    }
}
