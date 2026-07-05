<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Handler;

use App\Entity\Ses\SesEvent;
use App\Ses\Webhook\Command\ProcessSesEventCommand;
use App\Ses\Webhook\SesEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessSesEventHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SesEventDispatcher $dispatcher,
    ) {
    }

    public function __invoke(ProcessSesEventCommand $command): void
    {
        $event = $this->entityManager->getRepository(SesEvent::class)->findOneBy(['snsMessageId' => $command->snsMessageId]);
        if (!$event instanceof SesEvent) {
            return;
        }

        $this->dispatcher->dispatch($event->payload);
    }
}
