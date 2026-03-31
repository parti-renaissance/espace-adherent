<?php

declare(strict_types=1);

namespace App\ManagedUsers;

use App\Entity\Adherent;
use App\Entity\Projection\ManagedUser;
use App\Repository\AdherentMandate\ElectedRepresentativeAdherentMandateRepository;
use App\Repository\Projection\ManagedUserRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProjectionRefresher
{
    public function __construct(
        private readonly ManagedUserRepository $managedUserRepository,
        private readonly ElectedRepresentativeAdherentMandateRepository $mandateRepository,
        private readonly RoleDataBuilder $roleDataBuilder,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function refresh(Adherent $adherent): void
    {
        $managedUser = $this->managedUserRepository->findOneBy(['originalId' => $adherent->getId()]);

        if (!$managedUser instanceof ManagedUser) {
            return;
        }

        $managedUser->setRoles($this->roleDataBuilder->buildRoles($adherent));
        $managedUser->setElectMandates($this->buildElectMandates($adherent));

        $this->entityManager->flush();
    }

    private function buildElectMandates(Adherent $adherent): ?array
    {
        $mandateTypes = $this->mandateRepository->getAdherentMandateTypes($adherent);

        return $mandateTypes ?: null;
    }
}
