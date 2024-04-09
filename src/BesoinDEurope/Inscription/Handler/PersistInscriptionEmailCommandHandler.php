<?php

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
        if ($adherent = $this->adherentRepository->findOneByEmail($command->email)) {
            $this->membershipNotifier->sendConnexionDetailsMessage($adherent, AppCodeEnum::BESOIN_D_EUROPE);

            return null;
        }

        $this->entityManager->persist($object = InscriptionRequest::createForEmail($command->email));

        $object->utmCampaign = $command->utmCampaign;
        $object->utmSource = $command->utmSource;
        $object->clientIp = $command->clientIp;

        $this->entityManager->flush();

        return $object->email;
    }
}
