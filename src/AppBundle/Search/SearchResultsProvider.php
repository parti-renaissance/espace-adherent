<?php

namespace AppBundle\Search;

use AppBundle\Entity\Committee;
use AppBundle\Repository\CommitteeEventRepository;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;

class SearchResultsProvider
{
    private $committee;
    private $membership;
    private $event;

    public function __construct(
        CommitteeRepository $committee,
        CommitteeMembershipRepository $membership,
        CommitteeEventRepository $event
    ) {
        $this->committee = $committee;
        $this->membership = $membership;
        $this->event = $event;
    }

    public function find(SearchParametersFilter $search): array
    {
        if (SearchParametersFilter::TYPE_COMMITTEES === $search->getType()) {
            return $this->findCommittees($search);
        }

        if (SearchParametersFilter::TYPE_EVENTS === $search->getType()) {
            return $this->findEvents($search);
        }

        throw new \RuntimeException(sprintf(
            'This provider is not able to handle the search type "%s"',
            $search->getType()
        ));
    }

    public function findCommittees(SearchParametersFilter $search): array
    {
        if ($coordinates = $search->getCityCoordinates()) {
            $qb = $this->committee
                ->createNearbyQueryBuilder($coordinates)
                ->where($this->committee->getNearbyExpression().' < :distance_max')
                ->setParameter('distance_max', $search->getRadius())
            ;
        } else {
            $qb = $this->committee->createQueryBuilder('c');
        }

        $query = $search->getQuery();

        if (!empty($query)) {
            $qb->andWhere('n.name like :query');
            $qb->setParameter('query', '%'.$query.'%');
        }

        /** @var Committee[] $committees */
        $committees = $qb
            ->andWhere('n.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->setFirstResult($search->getOffset())
            ->setMaxResults($search->getMaxResults())
            ->getQuery()
            ->getResult()
        ;

        foreach ($committees as $committee) {
            $results[] = [
                'committee' => $committee,
                'memberships' => $this->membership->countMembers($committee->getUuid()),
            ];
        }

        return $results ?? [];
    }

    public function findEvents(SearchParametersFilter $search): array
    {
        if ($coordinates = $search->getCityCoordinates()) {
            $qb = $this->event
                ->createNearbyQueryBuilder($coordinates)
                ->where($this->committee->getNearbyExpression().' < :distance_max')
                ->setParameter('distance_max', $search->getRadius())
                ->orderBy('n.beginAt', 'asc')
                ->addOrderBy('distance_between', 'asc')
            ;
        } else {
            $qb = $this->committee->createQueryBuilder('n');
        }

        $query = $search->getQuery();

        if (!empty($query)) {
            $qb->andWhere('n.name like :query');
            $qb->setParameter('query', '%'.$query.'%');
        }

        /** @var Committee[] $committees */
        $events = $qb
            ->setFirstResult($search->getOffset())
            ->setMaxResults($search->getMaxResults())
            ->getQuery()
            ->getResult()
        ;

        foreach ($events as $event) {
            $results[] = [
                'event' => $event,
                'attendees' => 5, // Fixture
            ];
        }

        return $results ?? [];
    }
}
