<?php

namespace App\NationalEvent\Handler;

use App\Entity\NationalEvent\EventInscription;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\NationalEventReminderMessage;
use App\NationalEvent\Command\SendReminderCommand;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendReminderHandler
{
    public function __construct(
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerService $transactionalMailer,
    ) {
    }

    public function __invoke(SendReminderCommand $command): void
    {
        /** @var EventInscription $eventInscription */
        if (!$eventInscription = $this->eventInscriptionRepository->findOneByUuid($command->getUuid()->toString())) {
            return;
        }

        $this->entityManager->refresh($eventInscription);

        $this->transactionalMailer->sendMessage(NationalEventReminderMessage::create($eventInscription), false);

        $this->entityManager->flush();
    }
}
