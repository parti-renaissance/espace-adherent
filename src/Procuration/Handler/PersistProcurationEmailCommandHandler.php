<?php

declare(strict_types=1);

namespace App\Procuration\Handler;

use App\Entity\ProcurationV2\ProcurationRequest;
use App\Procuration\Command\PersistProcurationEmailCommand;
use App\Repository\AdherentRepository;
use App\Repository\Procuration\ProcurationRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PersistProcurationEmailCommandHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProcurationRequestRepository $procurationRequestRepository,
        private readonly AdherentRepository $adherentRepository,
    ) {
    }

    public function __invoke(PersistProcurationEmailCommand $command): ?string
    {
        $initialRequest = $this->findInitialRequest($command->getEmail());

        if (!$initialRequest) {
            $initialRequest = ProcurationRequest::createForEmail($command->getEmail());

            $this->entityManager->persist($initialRequest);
        }

        $initialRequest->type = $command->type;
        $initialRequest->utmCampaign = $command->utmCampaign;
        $initialRequest->utmSource = $command->utmSource;
        $initialRequest->clientIp = $command->clientIp;
        $initialRequest->adherent = $this->adherentRepository->findOneByEmail($command->getEmail());

        $this->entityManager->flush();

        return $initialRequest->email;
    }

    /** @return ProcurationRequest[]|array */
    private function findInitialRequest(string $email): ?ProcurationRequest
    {
        return $this->procurationRequestRepository
            ->createQueryBuilder('ir')
            ->where('ir.email = :email')
            ->setParameter('email', $email)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
