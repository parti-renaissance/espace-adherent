<?php

namespace App\Procuration\V2;

use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Procuration\V2\Command\ProxyCommand;
use App\Procuration\V2\Command\RequestCommand;
use App\Procuration\V2\Event\ProcurationEvents;
use App\Procuration\V2\Event\ProxyEvent;
use App\Repository\Procuration\ProcurationRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProcurationHandler
{
    public function __construct(
        private readonly ProcurationFactory $factory,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProcurationNotifier $notifier,
        private readonly MatchingHistoryHandler $matchingHistoryHandler,
        private readonly ProcurationRequestRepository $procurationRequestRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function handleRequest(RequestCommand $command): Request
    {
        $request = $this->factory->createRequestFromCommand($command);

        $this->entityManager->persist($request);
        $this->entityManager->flush();

        $this->notifier->sendRequestConfirmation($request);

        $this->cleanInitialRequests($request->email, InitialRequestTypeEnum::REQUEST);

        return $request;
    }

    public function handleProxy(ProxyCommand $command): Proxy
    {
        $proxy = $this->factory->createProxyFromCommand($command);

        $this->entityManager->persist($proxy);
        $this->entityManager->flush();

        $this->notifier->sendProxyConfirmation($proxy);

        $this->eventDispatcher->dispatch(new ProxyEvent($proxy), ProcurationEvents::PROXY_CREATED);

        $this->cleanInitialRequests($proxy->email, InitialRequestTypeEnum::PROXY);

        return $proxy;
    }

    public function updateRequestStatus(Request $request): void
    {
        if ($request->isPending() && $request->proxy instanceof Proxy) {
            $request->markAsCompleted();
        } elseif ($request->isCompleted() && !$request->proxy instanceof Proxy) {
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

    public function match(Request $request, Proxy $proxy): void
    {
        $proxy->addRequest($request);
        $this->entityManager->flush();

        $this->updateRequestStatus($request);
        $this->updateProxyStatus($proxy);

        $history = $this->matchingHistoryHandler->createMatch($request, $proxy);

        $this->notifier->sendMatchConfirmation($request, $proxy, $history->matcher);
    }

    public function unmatch(Request $request): void
    {
        if (!$proxy = $request->proxy) {
            return;
        }

        $proxy->removeRequest($request);
        $this->entityManager->flush();

        $this->updateRequestStatus($request);
        $this->updateProxyStatus($proxy);

        $history = $this->matchingHistoryHandler->createUnmatch($request, $proxy);

        $this->notifier->sendUnmatchConfirmation($request, $proxy, $history->matcher);
    }

    private function cleanInitialRequests(string $email, InitialRequestTypeEnum $type): void
    {
        $initialRequests = $this->procurationRequestRepository->findBy([
            'email' => $email,
            'type' => $type,
        ]);

        if (empty($initialRequests)) {
            return;
        }

        foreach ($initialRequests as $initialRequest) {
            $this->entityManager->remove($initialRequest);
        }

        $this->entityManager->flush();
    }
}
