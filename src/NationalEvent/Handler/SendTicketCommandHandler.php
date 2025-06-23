<?php

namespace App\NationalEvent\Handler;

use App\Entity\NationalEvent\EventInscription;
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
        return;

        /** @var EventInscription $eventInscription */
        if (!$eventInscription = $this->eventInscriptionRepository->findOneByUuid($command->getUuid()->toString())) {
            return;
        }

        $this->entityManager->refresh($eventInscription);

        $this->notifier->sendTicket($eventInscription);

        $eventInscription->ticketSentAt = new \DateTime();

        $this->entityManager->flush();
    }
}
