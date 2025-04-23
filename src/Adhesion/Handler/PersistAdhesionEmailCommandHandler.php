<?php

namespace App\Adhesion\Handler;

use App\Adhesion\Command\PersistAdhesionEmailCommand;
use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Membership\MembershipNotifier;
use App\Repository\AdherentRepository;
use App\Repository\Renaissance\Adhesion\AdherentRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PersistAdhesionEmailCommandHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdherentRepository $adherentRepository,
        private readonly AdherentRequestRepository $adherentRequestRepository,
        private readonly MembershipNotifier $membershipNotifier,
    ) {
    }

    public function __invoke(PersistAdhesionEmailCommand $command): ?string
    {
        if ($adherent = $this->adherentRepository->findOneByEmail($command->getEmail())) {
            $this->membershipNotifier->sendConnexionDetailsMessage($adherent);

            return null;
        }

        if (0 !== $this->adherentRequestRepository->count(['email' => $command->getEmail()])) {
            return $command->getEmail();
        }

        $this->entityManager->persist($object = AdherentRequest::createForEmail($command->getEmail()));

        $object->utmCampaign = $command->utmCampaign;
        $object->utmSource = $command->utmSource;

        $this->entityManager->flush();

        return $object->email;
    }
}
