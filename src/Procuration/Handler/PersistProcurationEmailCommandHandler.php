<?php

namespace App\Procuration\Handler;

use App\Entity\ProcurationV2\ProcurationRequest;
use App\Procuration\Command\PersistProcurationEmailCommand;
use App\Procuration\V2\InitialRequestTypeEnum;
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
        if ($procurationRequest = $this->findProcurationRequest($command->email, $command->type)) {
            return $procurationRequest->email;
        }

        $this->entityManager->persist($object = ProcurationRequest::createForEmail($command->email, $command->type));

        $object->utmCampaign = $command->utmCampaign;
        $object->utmSource = $command->utmSource;
        $object->clientIp = $command->clientIp;

        $this->entityManager->flush();

        return $object->email;
    }

    private function findProcurationRequest(string $email, InitialRequestTypeEnum $type): ?ProcurationRequest
    {
        return $this->procurationRequestRepository->findOneBy([
            'email' => $email,
            'type' => $type,
        ]);
    }
}
