<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Scope\ScopeEnum;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

trait AudienceFilterTrait
{
    public function applyAudienceFilter(AudienceFilter $filter, QueryBuilder $qb, string $mainAlias = 'a'): void
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
    }
}
