<?php

namespace App\AdherentMessage\Handler;

use App\AdherentMessage\Command\CreatePublicationReachFromPushCommand;
use App\Entity\AdherentMessage\AdherentMessageReach;
use App\Repository\AdherentMessageRepository;
use App\Repository\AppSessionRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreatePublicationReachFromPushCommandHandler
{
    public function __construct(
        private readonly AdherentMessageRepository $adherentMessageRepository,
        private readonly AppSessionRepository $appSessionRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CreatePublicationReachFromPushCommand $command): void
    {
        if (!$adherentMessage = $this->adherentMessageRepository->find($command->adherentMessageId)) {
            return;
        }

        if (!$session = $this->appSessionRepository->findByPushToken($command->pushToken)) {
            return;
        }

        $this->entityManager->persist(new AdherentMessageReach(
            $adherentMessage,
            $session->adherent,
            'push',
            $adherentMessage->getSentAt(),
        ));

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
        }
    }
}
