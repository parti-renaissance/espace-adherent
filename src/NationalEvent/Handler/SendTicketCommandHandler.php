<?php

namespace App\NationalEvent\Handler;

use App\Entity\NationalEvent\EventInscription;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\NationalEventTicketMessage;
use App\NationalEvent\Command\SendTicketCommand;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendTicketCommandHandler
{
    public function __construct(
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerService $transactionalMailer,
    ) {
    }

    public function __invoke(SendTicketCommand $command): void
    {
        /** @var EventInscription $eventInscription */
        if (!$eventInscription = $this->eventInscriptionRepository->findOneByUuid($command->getUuid()->toString())) {
            return;
        }

        $this->entityManager->refresh($eventInscription);

        $this->transactionalMailer->sendMessage(NationalEventTicketMessage::create($eventInscription), false);

        $eventInscription->ticketSentAt = new \DateTime();

        $this->entityManager->flush();
    }
}
