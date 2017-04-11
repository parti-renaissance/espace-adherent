<?php

namespace AppBundle\Procuration;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Procuration\Filter\ProcurationProxyProposalFilters;
use AppBundle\Procuration\Filter\ProcurationRequestFilters;
use AppBundle\Repository\ProcurationProxyRepository;
use AppBundle\Repository\ProcurationRequestRepository;
use Doctrine\Common\Persistence\ObjectManager;

class ProcurationManager
{
    private $procurationRequestRepository;
    private $procurationProxyRepository;
    private $manager;

    public function __construct(
        ProcurationRequestRepository $procurationRequestRepository,
        ProcurationProxyRepository $procurationProxyRepository,
        ObjectManager $manager
    ) {
        $this->procurationRequestRepository = $procurationRequestRepository;
        $this->procurationProxyRepository = $procurationProxyRepository;
        $this->manager = $manager;
    }

    public function processProcurationRequest(ProcurationRequest $request, bool $flush = true): void
    {
        $request->process();

        if ($flush) {
            $this->manager->flush();
        }
    }

    public function unprocessProcurationRequest(ProcurationRequest $request, bool $flush = true): void
    {
        $request->unprocess();

        if ($flush) {
            $this->manager->flush();
        }
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

    public function getMatchingProcurationProxies(ProcurationRequest $request): array
    {
        return $this->procurationProxyRepository->findMatchingProxies($request);
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
}
