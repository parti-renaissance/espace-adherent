<?php

namespace App\Procuration\V2;

use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Entity\ProcurationV2\Round;
use App\Procuration\V2\Command\ProxyCommand;
use App\Procuration\V2\Command\RequestCommand;
use App\Procuration\V2\Event\ProcurationEvent;
use App\Procuration\V2\Event\ProcurationEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProcurationHandler
{
    public function __construct(
        private readonly ProcurationFactory $factory,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProcurationNotifier $notifier,
        private readonly MatchingHistoryHandler $matchingHistoryHandler,
        private readonly EventDispatcherInterface $eventDispatcher
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
        $proxy->addRequest($request);
        $proxy->matchSlot($round, $request);
        $this->entityManager->flush();

        $this->updateRequestStatus($request);
        $this->updateProxyStatus($proxy);

        $history = $this->matchingHistoryHandler->createMatch($request, $proxy, $emailCopy);

        $this->notifier->sendMatchConfirmation($request, $proxy, $emailCopy ? $history->matcher : null);
    }

    public function unmatch(Request $request, Round $round, bool $emailCopy): void
    {
        if (!$proxy = $request->proxy) {
            return;
        }

        $proxy->removeRequest($request);
        $proxy->unmatchSlot($round, $request);
        $this->entityManager->flush();

        $this->updateRequestStatus($request);
        $this->updateProxyStatus($proxy);

        $history = $this->matchingHistoryHandler->createUnmatch($request, $proxy, $emailCopy);

        $this->notifier->sendUnmatchConfirmation($request, $proxy, $emailCopy ? $history->matcher : null);
    }
}
