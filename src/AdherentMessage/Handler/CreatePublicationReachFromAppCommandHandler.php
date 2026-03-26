<?php

declare(strict_types=1);

namespace App\AdherentMessage\Handler;

use App\AdherentMessage\Command\CreatePublicationReachFromAppCommand;
use App\Repository\AdherentMessageRepository;
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

        $this->entityManager->getConnection()->executeStatement(
            'INSERT IGNORE INTO adherent_message_reach (message_id, adherent_id, source, date) VALUES (?, ?, ?, ?)',
            [
                $adherentMessage->getId(),
                $command->adherentId,
                'app',
                $command->createdAt->format('Y-m-d H:i:s'),
            ]
        );
    }
}
