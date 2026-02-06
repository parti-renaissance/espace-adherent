<?php

declare(strict_types=1);

namespace App\Procuration\Handler;

use App\Entity\Procuration\ProxySlot;
use App\Entity\Procuration\RequestSlot;
use App\Procuration\Command\MatchedRequestSlotReminderCommand;
use App\Procuration\ProcurationNotifier;
use App\Repository\Procuration\RequestSlotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class MatchedRequestSlotReminderCommandHandler
{
    public function __construct(
        private readonly RequestSlotRepository $requestSlotRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProcurationNotifier $procurationNotifier,
    ) {
    }

    public function __invoke(MatchedRequestSlotReminderCommand $command): void
    {
        $requestSlot = $this->requestSlotRepository->findOneByUuid($command->getUuid());

        if (!$requestSlot instanceof RequestSlot) {
            return;
        }

        $round = $requestSlot->round;
        $matchedProxySlot = $requestSlot->proxySlot;

        if (
            !$round->isUpcoming()
            || $requestSlot->isMatchReminded()
            || !$matchedProxySlot instanceof ProxySlot
            || $requestSlot->manual
            || $matchedProxySlot->manual
        ) {
            return;
        }

        $this->procurationNotifier->sendMatchReminder(
            $requestSlot->request,
            $matchedProxySlot->proxy,
            $round,
            $requestSlot->getMatcher()
        );

        $requestSlot->remindMatch();

        $this->entityManager->flush();
    }
}
