<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\AuthorInstanceInterface;
use App\Entity\Committee;
use App\Entity\Event\Event;
use App\Entity\ZoneableEntityInterface;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeGeneratorResolver;

class ManageZoneableItemVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'MANAGE_ZONEABLE_ITEM__';

    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly ZoneRepository $zoneRepository,
    ) {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $scopeZones = [];
        $committeeUuids = [];
        $agoraUuids = [];

        if ($scope = $this->scopeGeneratorResolver->generate()) {
            $adherent = $scope->getDelegator() ?? $adherent;
            $scopeZones = $scope->getZones();
            $committeeUuids = $scope->getCommitteeUuids();
            $agoraUuids = $scope->getAgoraUuids();

            if ($scope->isNational()) {
                return true;
            }

            if ($subject instanceof AuthorInstanceInterface && $subject->getInstanceKey() === $scope->getInstanceKey()) {
                return true;
            }
        }

        // Committee/Agora-based access for Events
        if (!empty($committeeUuids) && $subject instanceof Event && $subject->getCommittee()) {
            return \in_array($subject->getCommitteeUuid(), $committeeUuids);
        }

        if (!empty($agoraUuids) && $subject instanceof Event && $subject->agora) {
            return \in_array($subject->agora->getUuid()->toString(), $agoraUuids);
        }

        // Committee-based access for Committee entities
        if (!empty($committeeUuids) && $subject instanceof Committee) {
            return \in_array($subject->getUuid()->toString(), $committeeUuids);
        }

        // Committee/Agora-based access for Adherents (via membership)
        if ($subject instanceof Adherent && $subject !== $adherent) {
            if (!empty($committeeUuids) && ($membership = $subject->getCommitteeMembership())) {
                if (\in_array($membership->getCommittee()->getUuid()->toString(), $committeeUuids)) {
                    return true;
                }
            }

            if (!empty($agoraUuids)) {
                foreach ($subject->agoraMemberships as $agoraMembership) {
                    if (\in_array($agoraMembership->agora->getUuid()->toString(), $agoraUuids)) {
                        return true;
                    }
                }
            }
        }

        if (empty($scopeZones)) {
            return false;
        }

        foreach ($subject->getZones() as $zone) {
            if ($this->zoneRepository->isInZones([$zone], $scopeZones)) {
                return true;
            }
        }

        return false;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return str_starts_with($attribute, self::PERMISSION) && $subject instanceof ZoneableEntityInterface;
    }
}
