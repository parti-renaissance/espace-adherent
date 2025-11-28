<?php

declare(strict_types=1);

namespace App\Adherent\Notification;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class NewMembershipNotificationCommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdherentRepository $adherentRepository,
        private readonly NewMembershipNotificationHandler $newMembershipNotificationHandler,
        LoggerInterface $logger,
    ) {
        $this->logger = $logger;
    }

    public function __invoke(NewMembershipNotificationCommand $command): void
    {
        /** @var Adherent|null $adherent */
        $adherent = $this->adherentRepository->findOneByUuid($command->getUuid());

        if (!$adherent) {
            return;
        }

        $this->entityManager->refresh($adherent);

        $from = $command->getFrom();
        $to = $command->getTo();

        try {
            $this->newMembershipNotificationHandler->handle($adherent, $from, $to);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['e' => $e]);

            throw $e;
        }

        $this->entityManager->clear();
    }
}
