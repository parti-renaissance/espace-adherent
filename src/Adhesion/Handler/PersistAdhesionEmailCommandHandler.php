<?php

namespace App\Adhesion\Handler;

use App\Adhesion\Command\PersistAdhesionEmailCommand;
use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Membership\MembershipNotifier;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PersistAdhesionEmailCommandHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdherentRepository $adherentRepository,
        private readonly MembershipNotifier $membershipNotifier,
    ) {
    }

    public function __invoke(PersistAdhesionEmailCommand $command): bool
    {
        if ($adherent = $this->adherentRepository->findOneByEmail($command->email)) {
            $this->membershipNotifier->sendConnexionDetailsMessage($adherent);

            return false;
        }

        $this->entityManager->persist(AdherentRequest::createForEmail($command->email));
        $this->entityManager->flush();

        return true;
    }
}
