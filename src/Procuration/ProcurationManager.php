<?php

namespace App\Procuration;

use App\Entity\Adherent;
use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use App\Procuration\Event\ProcurationEvents;
use App\Procuration\Event\ProcurationProxyEvent;
use App\Procuration\Event\ProcurationRequestEvent;
use App\Procuration\Filter\ProcurationProxyProposalFilters;
use App\Procuration\Filter\ProcurationRequestFilters;
use App\Repository\ProcurationProxyRepository;
use App\Repository\ProcurationRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProcurationManager
{
    private $procurationRequestRepository;
    private $procurationProxyRepository;
    private $manager;
    private $dispatcher;

    public function __construct(
        ProcurationRequestRepository $procurationRequestRepository,
        ProcurationProxyRepository $procurationProxyRepository,
        EntityManagerInterface $manager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->procurationRequestRepository = $procurationRequestRepository;
        $this->procurationProxyRepository = $procurationProxyRepository;
        $this->manager = $manager;
        $this->dispatcher = $dispatcher;
    }

    public function processProcurationRequest(
        ProcurationRequest $request,
        ProcurationProxy $proxy = null,
        Adherent $referent = null,
        bool $notify = false,
        bool $flush = true
    ): void {
        $request->process($proxy, $referent);

        if ($flush) {
            $this->manager->flush();
        }

        $this->dispatcher->dispatch(new ProcurationRequestEvent($request, $notify), ProcurationEvents::REQUEST_PROCESSED);
    }

    public function unprocessProcurationRequest(
        ProcurationRequest $request,
        Adherent $referent = null,
        bool $notify = false,
        bool $flush = true
    ): void {
        $request->unprocess();

        if ($flush) {
            $this->manager->flush();
        }

        $this->dispatcher->dispatch(new ProcurationRequestEvent($request, $notify, $referent), ProcurationEvents::REQUEST_UNPROCESSED);
    }

    public function enableProcurationProxy(ProcurationProxy $proxy, bool $flush = true): void
    {
        $proxy->enable();

        if ($flush) {
            $this->manager->flush();
        }
    }

    public function disableProcurationProxy(ProcurationProxy $proxy, bool $flush = true): void
    {
        $proxy->disable();

        if ($flush) {
            $this->manager->flush();
        }
    }

    public function enableProcurationRequest(ProcurationRequest $request): void
    {
        $request->enable();
        $this->manager->flush();
    }

    public function disableProcurationRequest(ProcurationRequest $request, ?string $reason = null): void
    {
        $request->disable($reason);
        $this->manager->flush();
    }

    public function getMatchingProcurationProxies(ProcurationRequest $request): array
    {
        return $this->procurationProxyRepository->findMatchingProxies($request);
    }

    public function getMatchingProcurationProxiesByOtherCities(ProcurationRequest $request): array
    {
        return $this->procurationProxyRepository->findMatchingProxiesByOtherCities($request);
    }

    public function getProcurationProxyProposal(int $id, Adherent $manager): ?ProcurationProxy
    {
        $proposal = $this->procurationProxyRepository->find($id);

        if (!$proposal instanceof ProcurationProxy) {
            return null;
        }

        if (!$this->procurationProxyRepository->isManagedBy($manager, $proposal)) {
            return null;
        }

        return $proposal;
    }

    public function getProcurationRequest(int $id, Adherent $manager): ?ProcurationRequest
    {
        $request = $this->procurationRequestRepository->find($id);

        if (!$request instanceof ProcurationRequest) {
            return null;
        }

        if (!$this->procurationRequestRepository->isManagedBy($manager, $request)) {
            return null;
        }

        return $request;
    }

    public function getProcurationRequests(Adherent $manager, ProcurationRequestFilters $filters): array
    {
        return $this->procurationRequestRepository->findMatchingRequests($manager, $filters);
    }

    public function countProcurationRequests(Adherent $manager, ProcurationRequestFilters $filters): int
    {
        return $this->procurationRequestRepository->countMatchingRequests($manager, $filters);
    }

    public function getProcurationProxyProposals(Adherent $manager, ProcurationProxyProposalFilters $filters): array
    {
        return $this->procurationProxyRepository->findMatchingProposals($manager, $filters);
    }

    public function countProcurationProxyProposals(Adherent $manager, ProcurationProxyProposalFilters $filters): int
    {
        return $this->procurationProxyRepository->countMatchingProposals($manager, $filters);
    }

    public function createProcurationProxy(ProcurationProxy $proxy): void
    {
        $this->manager->persist($proxy);
        $this->manager->flush();

        $this->dispatcher->dispatch(new ProcurationProxyEvent($proxy), ProcurationEvents::PROXY_REGISTRATION);
    }

    public function createProcurationRequest(ProcurationRequest $request): void
    {
        $this->manager->persist($request);
        $this->manager->flush();

        $this->dispatcher->dispatch(new ProcurationRequestEvent($request), ProcurationEvents::REQUEST_REGISTRATION);
    }
}
