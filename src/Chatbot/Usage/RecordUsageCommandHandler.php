<?php

declare(strict_types=1);

namespace App\Chatbot\Usage;

use App\Entity\Chatbot\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class RecordUsageCommandHandler
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(RecordUsageCommand $command): void
    {
        $message = $this->entityManager->find(Message::class, $command->messageId);

        if (!$message) {
            return;
        }

        $message->raw = [
            'usage' => $command->rawUsage,
            'response_time_ms' => $command->responseTimeMs,
        ];

        $this->entityManager->flush();
    }
}
