<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\AuthorInstanceInterface;
use App\Entity\Committee;
use App\Entity\Event\Event;
use App\Entity\Projection\ManagedUser;
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

        [$subjectCommitteeUuids, $subjectAgoraUuids] = $this->getSubjectScopeMemberships($subject, $adherent);

        $isCommitteeAgoraBoundResource = $subject instanceof Event || $subject instanceof Committee;

        if (!empty($committeeUuids) && !empty($subjectCommitteeUuids)) {
            if (array_intersect($committeeUuids, $subjectCommitteeUuids)) {
                return true;
            }

            if ($isCommitteeAgoraBoundResource) {
                return false;
            }
        }

        if (!empty($agoraUuids) && !empty($subjectAgoraUuids)) {
            if (array_intersect($agoraUuids, $subjectAgoraUuids)) {
                return true;
            }

            if ($isCommitteeAgoraBoundResource) {
                return false;
            }
        }

        if (empty($scopeZones)) {
            return false;
        }

        return array_any($subject->getZones()->toArray(), fn ($zone) => $this->zoneRepository->isInZones([$zone], $scopeZones));
    }

    protected function supports(string $attribute, $subject): bool
    {
        return str_starts_with($attribute, self::PERMISSION) && $subject instanceof ZoneableEntityInterface;
    }

    /**
     * Returns the committee and agora UUIDs (rfc4122) the subject is bound to, as [committeeUuids, agoraUuids].
     *
     * @return array{0: list<string>, 1: list<string>}
     */
    private function getSubjectScopeMemberships($subject, Adherent $adherent): array
    {
        if ($subject instanceof Event) {
            return [
                $subject->getCommittee() ? [$subject->getCommitteeUuid()] : [],
                $subject->agora ? [$subject->agora->getUuid()->toRfc4122()] : [],
            ];
        }

        if ($subject instanceof Committee) {
            return [[$subject->getUuid()->toRfc4122()], []];
        }

        if ($subject instanceof ManagedUser) {
            $committeeUuids = $subject->getCommitteeUuids() ?? [];
            if ($committeeUuid = $subject->getCommitteeUuid()) {
                $committeeUuids[] = $committeeUuid->toRfc4122();
            }

            return [
                array_values($committeeUuids),
                ($agoraUuid = $subject->getAgoraUuid()) ? [$agoraUuid->toRfc4122()] : [],
            ];
        }

        // An Adherent must never gain access to their own profile through their own memberships:
        // self-management is handled by the geographic fallback instead.
        if ($subject instanceof Adherent && $subject !== $adherent) {
            $committeeUuids = ($membership = $subject->getCommitteeMembership())
                ? [$membership->getCommittee()->getUuid()->toRfc4122()]
                : [];

            $agoraUuids = [];
            foreach ($subject->agoraMemberships as $agoraMembership) {
                $agoraUuids[] = $agoraMembership->agora->getUuid()->toRfc4122();
            }

            return [$committeeUuids, $agoraUuids];
        }

        return [[], []];
    }
}
