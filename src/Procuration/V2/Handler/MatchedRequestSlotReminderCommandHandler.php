<?php

declare(strict_types=1);

namespace App\Procuration\V2\Handler;

use App\Entity\ProcurationV2\ProxySlot;
use App\Entity\ProcurationV2\RequestSlot;
use App\Procuration\V2\Command\MatchedRequestSlotReminderCommand;
use App\Procuration\V2\ProcurationNotifier;
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
