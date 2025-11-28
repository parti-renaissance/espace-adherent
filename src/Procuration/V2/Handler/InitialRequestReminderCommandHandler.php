<?php

declare(strict_types=1);

namespace App\Procuration\V2\Handler;

use App\Entity\ProcurationV2\ProcurationRequest;
use App\Procuration\V2\Command\InitialRequestReminderCommand;
use App\Procuration\V2\ProcurationNotifier;
use App\Repository\Procuration\ProcurationRequestRepository;
use App\Repository\Procuration\ProxyRepository;
use App\Repository\Procuration\RequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class InitialRequestReminderCommandHandler
{
    public function __construct(
        private readonly ProcurationRequestRepository $initialRequestRepository,
        private readonly RequestRepository $requestRepository,
        private readonly ProxyRepository $proxyRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProcurationNotifier $procurationNotifier,
    ) {
    }

    public function __invoke(InitialRequestReminderCommand $command): void
    {
        $initialRequest = $this->initialRequestRepository->findOneByUuid($command->getUuid());

        if (!$initialRequest instanceof ProcurationRequest) {
            return;
        }

        $email = $initialRequest->email;
        $createdAt = $initialRequest->getCreatedAt();

        if (
            0 > $this->countProxiesAfter($email, $createdAt)
            || 0 > $this->countRequestsAfter($email, $createdAt)
        ) {
            $this->entityManager->remove($initialRequest);
            $this->entityManager->flush();

            return;
        }

        if ($initialRequest->isReminded()) {
            return;
        }

        $this->procurationNotifier->sendInitialRequestReminder($initialRequest);

        $initialRequest->remind();

        $this->entityManager->flush();
    }

    private function countProxiesAfter(string $email, \DateTimeInterface $createdAfter): int
    {
        return $this->proxyRepository
            ->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p)')
            ->where('p.email = :email')
            ->andWhere('p.createdAt > :created_after')
            ->setParameters([
                'email' => $email,
                'created_after' => $createdAfter,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function countRequestsAfter(string $email, \DateTimeInterface $createdAfter): int
    {
        return $this->requestRepository
            ->createQueryBuilder('r')
            ->select('COUNT(DISTINCT r)')
            ->where('r.email = :email')
            ->andWhere('r.createdAt > :created_after')
            ->setParameters([
                'email' => $email,
                'created_after' => $createdAfter,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
