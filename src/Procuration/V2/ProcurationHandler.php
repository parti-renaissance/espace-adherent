<?php

namespace App\Procuration\V2;

use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Procuration\V2\Command\ProxyCommand;
use App\Procuration\V2\Command\RequestCommand;
use Doctrine\ORM\EntityManagerInterface;

class ProcurationHandler
{
    public function __construct(
        private readonly ProcurationFactory $factory,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProcurationNotifier $notifier
    ) {
    }

    public function handleRequest(RequestCommand $command): Request
    {
        $request = $this->factory->createRequestFromCommand($command);

        $this->entityManager->persist($request);
        $this->entityManager->flush();

        $this->notifier->sendRequestConfirmation($request);

        return $request;
    }

    public function handleProxy(ProxyCommand $command): Proxy
    {
        $proxy = $this->factory->createProxyFromCommand($command);

        $this->entityManager->persist($proxy);
        $this->entityManager->flush();

        $this->notifier->sendProxyConfirmation($proxy);

        return $proxy;
    }

    public function updateRequestStatus(Request $request): void
    {
        if ($request->isPending() && $request->proxy instanceof Proxy) {
            $request->markAsCompleted();

            $this->entityManager->flush();

            return;
        }

        if ($request->isCompleted() && !$request->proxy instanceof Proxy) {
            $request->markAsPending();

            $this->entityManager->flush();
        }
    }

    public function updateProxyStatus(Proxy $proxy): void
    {
        if ($proxy->isPending() && !$proxy->hasFreeSlot()) {
            $proxy->markAsCompleted();

            $this->entityManager->flush();

            return;
        }

        if ($proxy->isCompleted() && $proxy->hasFreeSlot()) {
            $proxy->markAsPending();

            $this->entityManager->flush();
        }
    }
}
