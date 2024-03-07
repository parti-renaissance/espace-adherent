<?php

namespace App\Procuration\Handler;

use App\Entity\Procuration\ProcurationRequest;
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
        $this->entityManager->persist($object = ProcurationRequest::createForEmail($command->email));

        $object->utmCampaign = $command->utmCampaign;
        $object->utmSource = $command->utmSource;

        $this->entityManager->flush();

        return $object->email;
    }
}
