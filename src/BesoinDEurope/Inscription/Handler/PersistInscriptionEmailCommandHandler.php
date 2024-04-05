<?php

namespace App\BesoinDEurope\Inscription\Handler;

use App\BesoinDEurope\Inscription\Command\PersistInscriptionEmailCommand;
use App\Entity\BesoinDEurope\InscriptionRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PersistInscriptionEmailCommandHandler
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(PersistInscriptionEmailCommand $command): ?string
    {
        $this->entityManager->persist($object = InscriptionRequest::createForEmail($command->email));

        $object->utmCampaign = $command->utmCampaign;
        $object->utmSource = $command->utmSource;
        $object->clientIp = $command->clientIp;

        $this->entityManager->flush();

        return $object->email;
    }
}
