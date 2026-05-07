<?php

declare(strict_types=1);

namespace App\AdherentMessage\Handler;

use App\AdherentMessage\AdherentMessageManager;
use App\AdherentMessage\Command\SendAdherentMessageCommand;
use App\Entity\AdherentMessage\AdherentMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendAdherentMessageCommandHandler
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdherentMessageManager $manager,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function __invoke(SendAdherentMessageCommand $command): void
    {
        $message = $this->entityManager->find(AdherentMessage::class, $command->adherentMessageId);
        if (null === $message) {
            $this->logger->warning('[SendAdherentMessage] AdherentMessage not found', ['id' => $command->adherentMessageId]);

            return;
        }

        if ($message->isSent()) {
            return;
        }

        try {
            $this->manager->send($message, $this->manager->getRecipients($message));
        } catch (\Throwable $e) {
            $this->logger->error('[SendAdherentMessage] send failed', [
                'message_id' => $command->adherentMessageId,
                'message_uuid' => $message->getUuid()->toString(),
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
