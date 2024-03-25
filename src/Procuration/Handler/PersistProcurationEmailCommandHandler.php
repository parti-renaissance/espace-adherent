<?php

namespace App\Procuration\Handler;

use App\Entity\ProcurationV2\ProcurationRequest;
use App\Procuration\Command\PersistProcurationEmailCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PersistProcurationEmailCommandHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(PersistProcurationEmailCommand $command): ?string
    {
        $this->entityManager->persist($object = ProcurationRequest::createForEmail($command->email, $command->type));

        $object->utmCampaign = $command->utmCampaign;
        $object->utmSource = $command->utmSource;
        $object->clientIp = $command->clientIp;

        $this->entityManager->flush();

        return $object->email;
    }
}
