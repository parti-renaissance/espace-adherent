<?php

namespace App\Procuration\V2\Listener;

use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\Request;
use App\Procuration\V2\Event\ProcurationEvent;
use App\Procuration\V2\Event\ProcurationEvents;
use App\Repository\Procuration\ProxyRepository;
use App\Repository\Procuration\RequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class DuplicationDetectorListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestRepository $requestRepository,
        private readonly ProxyRepository $proxyRepository,
        private readonly RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProcurationEvents::PROXY_CREATED => ['detectDuplicate', -255],
            ProcurationEvents::REQUEST_CREATED => ['detectDuplicate', -255],
        ];
    }

    public function detectDuplicate(ProcurationEvent $event): void
    {
        $procuration = $event->procuration;
        $duplicates = $otherSideDuplicates = $flush = null;

        $params = [
            $procuration->firstNames,
            $procuration->lastName,
            $procuration->birthdate,
            $procuration->getRounds(),
        ];

        if ($procuration instanceof Request) {
            if (!$duplicates = $this->requestRepository->findDuplicate($procuration->getId(), ...$params)) {
                $otherSideDuplicates = $this->proxyRepository->findDuplicate(null, ...$params);
            }
        } elseif ($procuration instanceof Proxy) {
            if (!$duplicates = $this->proxyRepository->findDuplicate($procuration->getId(), ...$params)) {
                $otherSideDuplicates = $this->requestRepository->findDuplicate(null, ...$params);
            }
        }

        if ($duplicates) {
            $procuration->markAsDuplicate(sprintf('%s doublon', $procuration instanceof Request ? 'Mandant' : 'Mandataire'));
            $flush = true;
        } elseif ($otherSideDuplicates) {
            $procuration->markAsDuplicate(sprintf('%s doublon opposé', $procuration instanceof Request ? 'Mandataire' : 'Mandant'));
            $flush = true;
        }

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
