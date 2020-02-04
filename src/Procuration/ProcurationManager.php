<?php

namespace AppBundle\Procuration;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Procuration\Event\ProcurationEvents;
use AppBundle\Procuration\Event\ProcurationProxyEvent;
use AppBundle\Procuration\Event\ProcurationRequestEvent;
use AppBundle\Procuration\Filter\ProcurationProxyProposalFilters;
use AppBundle\Procuration\Filter\ProcurationRequestFilters;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\ProcurationProxyRepository;
use AppBundle\Repository\ProcurationRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProcurationManager
{
    private $procurationRequestRepository;
    private $procurationProxyRepository;
    private $adherentRepository;
    private $manager;
    private $dispatcher;

    public function __construct(
        ProcurationRequestRepository $procurationRequestRepository,
        ProcurationProxyRepository $procurationProxyRepository,
        AdherentRepository $adherentRepository,
        EntityManagerInterface $manager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->procurationRequestRepository = $procurationRequestRepository;
        $this->procurationProxyRepository = $procurationProxyRepository;
        $this->adherentRepository = $adherentRepository;
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

        $this->dispatcher->dispatch(ProcurationEvents::REQUEST_PROCESSED, new ProcurationRequestEvent($request, $notify));
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

        $this->dispatcher->dispatch(ProcurationEvents::REQUEST_UNPROCESSED, new ProcurationRequestEvent($request, $notify, $referent));
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

    public function createProcurationProxy(ProcurationProxy $proxy): void
    {
        $this->processReliability($proxy);

        $this->manager->persist($proxy);
        $this->manager->flush();

        $this->dispatcher->dispatch(ProcurationEvents::PROXY_REGISTRATION, new ProcurationProxyEvent($proxy));
    }

    public function createProcurationRequest(ProcurationRequest $request): void
    {
        $this->manager->persist($request);
        $this->manager->flush();

        $this->dispatcher->dispatch(ProcurationEvents::REQUEST_REGISTRATION, new ProcurationRequestEvent($request));
    }

    private function processReliability(ProcurationProxy $proxy): void
    {
        if (!$adherent = $this->adherentRepository->findOneByEmail($proxy->getEmailAddress())) {
            return;
        }

        if (
            $adherent->isReferent()
            || $adherent->isCoReferent()
            || $adherent->isSenator()
            || $adherent->isDeputy()
            || $adherent->isCoordinator()
            || $adherent->isMunicipalChief()
        ) {
            $proxy->setRepresentativeReliability();

            return;
        }

        if (
            $adherent->isHost()
            || $adherent->isCitizenProjectAdministrator()
            || $adherent->isCoordinatorCommitteeSector()
            || $adherent->isCoordinatorCitizenProjectSector()
            || $adherent->isJecouteManager()
            || $adherent->isAssessorManager()
            || $adherent->isProcurationManager()
        ) {
            $proxy->setActivistReliability();

            return;
        }

        $proxy->setAdherentReliability();
    }
}
