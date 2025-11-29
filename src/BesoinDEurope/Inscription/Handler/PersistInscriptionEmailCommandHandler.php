<?php

declare(strict_types=1);

namespace App\BesoinDEurope\Inscription\Handler;

use App\AppCodeEnum;
use App\BesoinDEurope\Inscription\Command\PersistInscriptionEmailCommand;
use App\Entity\BesoinDEurope\InscriptionRequest;
use App\Membership\MembershipNotifier;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PersistInscriptionEmailCommandHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdherentRepository $adherentRepository,
        private readonly MembershipNotifier $membershipNotifier,
    ) {
    }

    public function __invoke(PersistInscriptionEmailCommand $command): ?string
    {
        if ($adherent = $this->adherentRepository->findOneByEmail($command->getEmail())) {
            $this->membershipNotifier->sendConnexionDetailsMessage($adherent, AppCodeEnum::LEGISLATIVE);

            return null;
        }

        $this->entityManager->persist($object = InscriptionRequest::createForEmail($command->getEmail()));

        $object->utmCampaign = $command->utmCampaign;
        $object->utmSource = $command->utmSource;
        $object->clientIp = $command->clientIp;

        $this->entityManager->flush();

        return $object->email;
    }
}
