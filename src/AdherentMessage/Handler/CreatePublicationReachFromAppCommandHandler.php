<?php

declare(strict_types=1);

namespace App\AdherentMessage\Handler;

use App\AdherentMessage\Command\CreatePublicationReachFromAppCommand;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageReach;
use App\Repository\AdherentMessageRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreatePublicationReachFromAppCommandHandler
{
    public function __construct(
        private readonly AdherentMessageRepository $adherentMessageRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(CreatePublicationReachFromAppCommand $command): void
    {
        if (!$adherentMessage = $this->adherentMessageRepository->findOneByUuid($command->publicationUuid)) {
            return;
        }

        $this->entityManager->persist(AdherentMessageReach::createApp(
            $adherentMessage,
            $this->entityManager->getReference(Adherent::class, $command->adherentId),
            $command->createdAt,
        ));

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
        }
    }
}
