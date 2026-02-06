<?php

declare(strict_types=1);

namespace App\Procuration;

use App\Entity\Procuration\Proxy;
use App\Entity\Procuration\ProxySlot;
use App\Entity\Procuration\Request;
use App\Entity\Procuration\RequestSlot;
use App\Entity\Procuration\Round;
use App\Procuration\Command\ProxyCommand;
use App\Procuration\Command\RequestCommand;
use App\Procuration\Event\ProcurationEvent;
use App\Procuration\Event\ProcurationEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProcurationHandler
{
    public function __construct(
        private readonly ProcurationActionHandler $actionHandler,
        private readonly ProcurationFactory $factory,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProcurationNotifier $notifier,
        private readonly MatchingHistoryHandler $matchingHistoryHandler,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handleRequest(RequestCommand $command): Request
    {
        $request = $this->factory->createRequestFromCommand($command);

        $this->entityManager->persist($request);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new ProcurationEvent($request), ProcurationEvents::REQUEST_CREATED);

        return $request;
    }

    public function handleProxy(ProxyCommand $command): Proxy
    {
        $proxy = $this->factory->createProxyFromCommand($command);

        $this->entityManager->persist($proxy);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new ProcurationEvent($proxy), ProcurationEvents::PROXY_CREATED);

        return $proxy;
    }

    public function updateRequestStatus(Request $request): void
    {
        if ($request->isPending() && !$request->hasFreeSlot()) {
            $request->markAsCompleted();
        } elseif ($request->isCompleted() && $request->hasFreeSlot()) {
            $request->markAsPending();
        }

        $this->entityManager->flush();
    }

    public function updateProxyStatus(Proxy $proxy): void
    {
        if ($proxy->isPending() && !$proxy->hasFreeSlot()) {
            $proxy->markAsCompleted();
        } elseif ($proxy->isCompleted() && $proxy->hasFreeSlot()) {
            $proxy->markAsPending();
        }

        $this->entityManager->flush();
    }

    public function match(Request $request, Proxy $proxy, Round $round, bool $emailCopy): void
    {
        $this->matchSlots($request, $proxy, $round);

        $this->updateRequestStatus($request);
        $this->updateProxyStatus($proxy);

        $history = $this->matchingHistoryHandler->createMatch($request, $proxy, $round, $emailCopy);

        $this->notifier->sendMatchConfirmation($request, $proxy, $round, $emailCopy ? $history->matcher : null);
    }

    public function unmatch(Request $request, Round $round, bool $emailCopy): void
    {
        $proxy = null;
        foreach ($request->requestSlots as $requestSlot) {
            if ($round === $requestSlot->round) {
                $proxy = $requestSlot->proxySlot?->proxy;

                break;
            }
        }

        if (!$proxy) {
            return;
        }

        $this->unmatchSlots($request, $proxy, $round);

        $this->updateRequestStatus($request);
        $this->updateProxyStatus($proxy);

        $history = $this->matchingHistoryHandler->createUnmatch($request, $proxy, $round, $emailCopy);

        $this->notifier->sendUnmatchConfirmation($request, $proxy, $round, $emailCopy ? $history->matcher : null);
    }

    private function matchSlots(Request $request, Proxy $proxy, Round $round): void
    {
        /** @var RequestSlot|null $requestSlot */
        $requestSlot = $request->requestSlots->filter(
            static function (RequestSlot $requestSlot) use ($round): bool {
                return $round === $requestSlot->round && null === $requestSlot->proxySlot;
            }
        )->first() ?? null;

        /** @var ProxySlot|null $proxySlot */
        $proxySlot = $proxy->proxySlots->filter(
            static function (ProxySlot $proxySlot) use ($round): bool {
                return $round === $proxySlot->round && null === $proxySlot->requestSlot;
            }
        )->first() ?? null;

        if (!$requestSlot || !$proxySlot) {
            return;
        }

        $requestSlot->match($proxySlot);
        $proxySlot->match($requestSlot);

        $this->entityManager->flush();

        $this->actionHandler->createMatchActions($requestSlot, $proxySlot);
    }

    private function unmatchSlots(Request $request, Proxy $proxy, Round $round): void
    {
        /** @var ProxySlot|null $proxySlot */
        $proxySlot = $proxy->proxySlots->filter(
            function (ProxySlot $proxySlot) use ($round, $request): bool {
                return $round === $proxySlot->round && $request === $proxySlot->requestSlot?->request;
            }
        )->first() ?? null;

        /** @var RequestSlot|null $requestSlot */
        $requestSlot = $request->requestSlots->filter(
            function (RequestSlot $requestSlot) use ($round, $proxy): bool {
                return $round === $requestSlot->round && $proxy === $requestSlot->proxySlot?->proxy;
            }
        )->first() ?? null;

        if (!$requestSlot || !$proxySlot) {
            return;
        }

        $requestSlot->unmatch();
        $proxySlot->unmatch();

        $this->entityManager->flush();

        $this->actionHandler->createUnmatchActions($requestSlot, $proxySlot);
    }
}
