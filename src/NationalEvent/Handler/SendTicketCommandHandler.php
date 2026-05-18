<?php

declare(strict_types=1);

namespace App\NationalEvent\Handler;

use App\NationalEvent\Command\SendTicketCommand;
use App\NationalEvent\Notifier;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendTicketCommandHandler
{
    public function __construct(
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Notifier $notifier,
    ) {
    }

    public function __invoke(SendTicketCommand $command): void
    {
        if (!$eventInscription = $this->eventInscriptionRepository->findOneByUuid($command->getUuid()->toRfc4122())) {
            return;
        }

        $this->entityManager->refresh($eventInscription);

        $this->notifier->sendTicket($eventInscription);

        $eventInscription->ticketSentAt = new \DateTime();

        $this->entityManager->flush();
    }
}
