<?php

declare(strict_types=1);

namespace App\Repository;

use App\Adherent\Authorization\ZoneBasedRoleTypeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentZoneBasedRole;
use App\Entity\MyTeam\DelegatedAccess;
use App\Scope\ScopeEnum;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

trait AudienceFilterTrait
{
    public function applyAudienceFilter(AdherentMessageFilter $filter, QueryBuilder $qb, string $mainAlias = 'a'): void
    {
        $perimeterFilterWasApplied = false;

        $zones = $filter->getZones();
        if (!$zones->isEmpty()) {
            $this->withGeoZones($zones->toArray(), $qb, $mainAlias, Adherent::class, 'a2', 'zones', 'z2', zoneParentAlias: 'zone_parent2');
            $perimeterFilterWasApplied = true;
        }

        if ($filter->getZone()) {
            $this->withGeoZones([$filter->getZone()], $qb, $mainAlias, Adherent::class, 'a3', 'zones', 'z3', zoneParentAlias: 'zone_parent3');
            $perimeterFilterWasApplied = true;
        }

        if ($filter->getCommittee()) {
            $qb
                ->innerJoin($mainAlias.'.committeeMembership', 'cm', Join::WITH, 'cm.committee = :committee')
                ->setParameter('committee', $filter->getCommittee())
            ;
            $perimeterFilterWasApplied = true;
        }

        if (!$perimeterFilterWasApplied && !ScopeEnum::isNational($filter->getScope())) {
            $qb->andWhere('1 = 0'); // No adherents if no perimeter filter was applied

            return;
        }

        // Use different condition for adherentTags to improve performance with LIKE
        if ($filter->adherentTags) {
            $operator = str_starts_with($filter->adherentTags, '!') ? 'NOT LIKE' : 'LIKE';

            $qb
                ->andWhere($mainAlias.'.tags '.$operator.' :tag_adherent')
                ->setParameter('tag_adherent', $filter->adherentTags.'%')
            ;
        }

        foreach (array_filter([$filter->electTags, $filter->staticTags]) as $index => $tag) {
            $operator = str_starts_with($tag, '!') ? 'NOT LIKE' : 'LIKE';

            $qb
                ->andWhere($mainAlias.'.tags '.$operator.' :tag_'.$index)
                ->setParameter('tag_'.$index, '%'.$tag.'%')
            ;
        }

        if ($registeredSince = $filter->getRegisteredSince()) {
            $qb
                ->andWhere($mainAlias.'.registeredAt >= :registered_since')
                ->setParameter('registered_since', $registeredSince->format('Y-m-d 00:00:00'))
            ;
        }

        if ($registeredUntil = $filter->getRegisteredUntil()) {
            $qb
                ->andWhere($mainAlias.'.registeredAt <= :registered_until')
                ->setParameter('registered_until', $registeredUntil->format('Y-m-d 23:59:59'))
            ;
        }

        if ($firstMembershipSince = $filter->firstMembershipSince) {
            $qb
                ->andWhere($mainAlias.'.firstMembershipDonation >= :first_membership_since')
                ->setParameter('first_membership_since', $firstMembershipSince->format('Y-m-d 00:00:00'))
            ;
        }

        if ($firstMembershipBefore = $filter->firstMembershipBefore) {
            $qb
                ->andWhere($mainAlias.'.firstMembershipDonation <= :first_membership_before')
                ->setParameter('first_membership_before', $firstMembershipBefore->format('Y-m-d 23:59:59'))
            ;
        }

        if ($lastMembershipSince = $filter->getLastMembershipSince()) {
            $qb
                ->andWhere($mainAlias.'.lastMembershipDonation >= :last_membership_since')
                ->setParameter('last_membership_since', $lastMembershipSince->format('Y-m-d 00:00:00'))
            ;
        }

        if ($lastMembershipBefore = $filter->getLastMembershipBefore()) {
            $qb
                ->andWhere($mainAlias.'.lastMembershipDonation <= :last_membership_before')
                ->setParameter('last_membership_before', $lastMembershipBefore->format('Y-m-d 23:59:59'))
            ;
        }

        if ($gender = $filter->getGender()) {
            $qb
                ->andWhere($mainAlias.'.gender = :gender')
                ->setParameter('gender', $gender)
            ;
        }

        if ($ageMin = $filter->getAgeMin()) {
            $qb
                ->andWhere($mainAlias.'.birthdate <= :min_birth_date')
                ->setParameter('min_birth_date', new \DateTimeImmutable()->sub(new \DateInterval(\sprintf('P%dY', $ageMin)))->format('Y-m-d'))
            ;
        }

        if ($ageMax = $filter->getAgeMax()) {
            $qb
                ->andWhere($mainAlias.'.birthdate >= :max_birth_date')
                ->setParameter('max_birth_date', new \DateTimeImmutable()->sub(new \DateInterval(\sprintf('P%dY', $ageMax)))->format('Y-m-d'))
            ;
        }

        if (null !== $filter->getIsCertified()) {
            $qb->andWhere($mainAlias.'.certifiedAt '.($filter->getIsCertified() ? 'IS NOT NULL' : 'IS NULL'));
        }

        if ($postalCode = $filter->postalCode) {
            $qb
                ->andWhere($mainAlias.'.postAddress.postalCode LIKE :postal_code')
                ->setParameter('postal_code', $postalCode.'%')
            ;
        }

        if ($electMandate = $filter->getElectMandate()) {
            $qb
                ->innerJoin($mainAlias.'.adherentMandates', 'am_filter', Join::WITH, 'am_filter INSTANCE OF :mandate_class AND am_filter.mandateType = :mandate_type')
                ->setParameter('mandate_class', ElectedRepresentativeAdherentMandate::class)
                ->setParameter('mandate_type', $electMandate)
            ;
        }

        if (null !== $filter->getIsCommitteeMember()) {
            $qb->andWhere($mainAlias.'.committeeMembership '.($filter->getIsCommitteeMember() ? 'IS NOT NULL' : 'IS NULL'));
        }

        if (!empty($filter->scopeTargets)) {
            $this->applyScopeTargetsFilter($filter->scopeTargets, $qb, $mainAlias);
        }
    }

    private function applyScopeTargetsFilter(array $scopeTargets, QueryBuilder $qb, string $mainAlias): void
    {
        $orConditions = [];
        $paramIndex = 0;

        foreach ($scopeTargets as $target) {
            $role = $target['role'] ?? null;
            $includeRole = $target['include_role'] ?? false;
            $includeTeam = $target['include_team'] ?? false;

            if (!$role) {
                continue;
            }

            if ($includeRole && \in_array($role, ZoneBasedRoleTypeEnum::ALL, true)) {
                $roleParam = 'st_role_'.$paramIndex;
                $orConditions[] = \sprintf(
                    'EXISTS (SELECT 1 FROM %s zbr_st WHERE zbr_st.adherent = %s AND zbr_st.type = :%s)',
                    AdherentZoneBasedRole::class,
                    $mainAlias,
                    $roleParam
                );
                $qb->setParameter($roleParam, $role);
                ++$paramIndex;
            }

            if ($includeTeam) {
                $typeParam = 'st_type_'.$paramIndex;
                $orConditions[] = \sprintf(
                    'EXISTS (SELECT 1 FROM %s da_st WHERE da_st.delegated = %s AND da_st.type = :%s)',
                    DelegatedAccess::class,
                    $mainAlias,
                    $typeParam
                );
                $qb->setParameter($typeParam, $role);
                ++$paramIndex;
            }
        }

        if (!empty($orConditions)) {
            $qb->andWhere('('.implode(' OR ', $orConditions).')');
        } else {
            $qb->andWhere('1 = 0');
        }
    }
}
