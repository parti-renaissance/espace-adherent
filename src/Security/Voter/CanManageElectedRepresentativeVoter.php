<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeGeneratorResolver;

class CanManageElectedRepresentativeVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_MANAGE_ELECTED_REPRESENTATIVE';

    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly ZoneRepository $zoneRepository
    ) {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope || !$subject instanceof ElectedRepresentative) {
            return false;
        }

        if (($owner = $subject->getCreatedByAdherent()) && $owner === $adherent) {
            return true;
        }

        if ($electedAdherent = $subject->getAdherent()) {
            if (0 === $electedAdherent->getZones()->count()) {
                return false;
            }

            return $this->zoneRepository->isInZones($electedAdherent->getZones()->toArray(), $scope->getZones());
        }

        return false;
    }

    protected function supports(string $attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof ElectedRepresentative;
    }
}
