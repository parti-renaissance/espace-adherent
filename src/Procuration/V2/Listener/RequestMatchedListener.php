<?php

declare(strict_types=1);

namespace App\Procuration\V2\Listener;

use App\Entity\ProcurationV2\Request;
use App\Procuration\V2\Event\ProcurationEvent;
use App\Procuration\V2\Event\ProcurationEvents;
use App\Procuration\V2\MatchingHistoryHandler;
use App\Procuration\V2\ProcurationNotifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestMatchedListener implements EventSubscriberInterface
{
    private array $proxiesBeforeUpdate = [];

    public function __construct(
        private readonly ProcurationNotifier $procurationNotifier,
        private readonly MatchingHistoryHandler $matchingHistoryHandler,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProcurationEvents::REQUEST_BEFORE_UPDATE => ['onBeforeUpdate', -256],
            ProcurationEvents::REQUEST_AFTER_UPDATE => ['onAfterUpdate', -256],
        ];
    }

    public function onBeforeUpdate(ProcurationEvent $event): void
    {
        $request = $event->procuration;

        if (!$request instanceof Request) {
            return;
        }

        foreach ($request->requestSlots as $requestSlot) {
            $this->proxiesBeforeUpdate[$requestSlot->round->getUuid()->toString()] = [
                'proxy' => $requestSlot->proxySlot?->proxy,
                'round' => $requestSlot->round,
            ];
        }
    }

    public function onAfterUpdate(ProcurationEvent $event): void
    {
        $request = $event->procuration;

        if (!$request instanceof Request) {
            return;
        }

        $proxiesAfterUpdate = [];
        foreach ($request->requestSlots as $requestSlot) {
            $proxiesAfterUpdate[$requestSlot->round->getUuid()->toString()] = [
                'proxy' => $requestSlot->proxySlot?->proxy,
                'round' => $requestSlot->round,
            ];
        }

        foreach ($this->proxiesBeforeUpdate as $roundUuid => $row) {
            if (
                $row['proxy']
                && (
                    !\array_key_exists($roundUuid, $proxiesAfterUpdate)
                    || $row['proxy'] !== $proxiesAfterUpdate[$roundUuid]['proxy']
                )
            ) {
                $round = $proxiesAfterUpdate[$roundUuid]['round'];

                $this->matchingHistoryHandler->createUnmatch($request, $row['proxy'], $round, false);

                $this->procurationNotifier->sendUnmatchConfirmation($request, $row['proxy'], $round);
            }
        }

        foreach ($request->requestSlots as $requestSlot) {
            $roundUuid = $requestSlot->round->getUuid()->toString();
            $proxy = $requestSlot->proxySlot?->proxy;

            if (
                $proxy
                && (
                    !\array_key_exists($roundUuid, $this->proxiesBeforeUpdate)
                    || $proxy !== $this->proxiesBeforeUpdate[$roundUuid]['proxy']
                )
            ) {
                $round = $this->proxiesBeforeUpdate[$roundUuid]['round'];

                $this->matchingHistoryHandler->createMatch($request, $proxy, $round, false);

                $this->procurationNotifier->sendMatchConfirmation($request, $proxy, $round);
            }
        }
    }
}
