<?php

namespace App\Procuration\Handler;

use App\Entity\ProcurationV2\ProcurationRequest;
use App\Procuration\Command\PersistProcurationEmailCommand;
use App\Repository\Procuration\ProcurationRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PersistProcurationEmailCommandHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProcurationRequestRepository $procurationRequestRepository
    ) {
    }

    public function __invoke(PersistProcurationEmailCommand $command): ?string
    {
        $initialRequest = $this->findInitialRequest($command->email);

        if (!$initialRequest) {
            $initialRequest = ProcurationRequest::createForEmail($command->email);

            $this->entityManager->persist($initialRequest);
        }

        $initialRequest->type = $command->type;
        $initialRequest->utmCampaign = $command->utmCampaign;
        $initialRequest->utmSource = $command->utmSource;
        $initialRequest->clientIp = $command->clientIp;

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
