<?php

namespace AppBundle\Search;

use AppBundle\Entity\Committee;
use AppBundle\Repository\EventRepository;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;
use Doctrine\ORM\QueryBuilder;

class SearchResultsProvider
{
    private $committee;
    private $membership;
    private $event;

    public function __construct(
        CommitteeRepository $committee,
        CommitteeMembershipRepository $membership,
        EventRepository $event
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
                ->andWhere($this->committee->getNearbyExpression().' < :distance_max')
                ->setParameter('distance_max', $search->getRadius())
            ;
        } else {
            $qb = $this->committee->createQueryBuilder('c');
        }

        $qb = $this->applySearchQueryQB($qb, $search);

        return $qb
            ->andWhere('n.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->setFirstResult($search->getOffset())
            ->setMaxResults($search->getMaxResults())
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEvents(SearchParametersFilter $search): array
    {
        if ($coordinates = $search->getCityCoordinates()) {
            $qb = $this->event
                ->createNearbyQueryBuilder($coordinates)
                ->andWhere($this->committee->getNearbyExpression().' < :distance_max')
                ->andWhere('n.beginAt > :now')
                ->setParameter('distance_max', $search->getRadius())
                ->setParameter('now', new \DateTime())
                ->orderBy('n.beginAt', 'asc')
                ->addOrderBy('distance_between', 'asc')
            ;
        } else {
            $qb = $this->committee->createQueryBuilder('n');
        }

        $qb = $this->applySearchQueryQB($qb, $search);

        return $qb
            ->setFirstResult($search->getOffset())
            ->setMaxResults($search->getMaxResults())
            ->getQuery()
            ->getResult()
        ;
    }

    private function applySearchQueryQB(QueryBuilder $qb, SearchParametersFilter $search): QueryBuilder
    {
        $query = $search->getQuery();

        if (empty($query)) {
            return $qb;
        }

        if ($search->containsPostalCodes()) {
            $qb->andWhere('n.postAddress.postalCode IN (:postalCodes)');
            $qb->setParameter('postalCodes', explode(',', $query));
        } else {
            $qb->andWhere('n.name like :query');
            $qb->setParameter('query', '%'.$query.'%');
        }

        return $qb;
    }
}
