<?php

namespace AppBundle\Repository;

use AppBundle\Assessor\Filter\AssessorRequestFilters;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AssessorRequest;
use AppBundle\Entity\VotePlace;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AssessorRequestRepository extends AbstractAssessorRepository
{
    private const ALIAS = 'ar';

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AssessorRequest::class);
    }

    /**
     * @return AssessorRequest[]
     */
    public function findAllProcessedManagedRequests(Adherent $manager): array
    {
        if (!$manager->isAssessorManager()) {
            throw new \InvalidArgumentException('Adherent must be an assessor manager.');
        }

        $qb = $this->createQueryBuilder(self::ALIAS);

        self::addAndWhereManagedBy($qb, $manager);

        return $qb
            ->addSelect('vp')
            ->leftJoin(self::ALIAS.'.votePlace', 'vp')
            ->andWhere(self::ALIAS.'.processed = true')
            ->andWhere(self::ALIAS.'.enabled = true')
            ->addOrderBy('vp.country', 'DESC')
            ->addOrderBy('vp.code', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findMatchingRequests(Adherent $manager, AssessorRequestFilters $filters): array
    {
        if (!$manager->isAssessorManager()) {
            return [];
        }

        $qb = $this->createQueryBuilder($alias = self::ALIAS);

        $filters->apply($qb, $alias);

        self::addAndWhereManagedBy($qb, $manager);

        $requests = $qb
            ->orderBy("$alias.processed", 'ASC')
            ->addOrderBy("$alias.createdAt", 'DESC')
            ->addOrderBy("$alias.lastName", 'ASC')
            ->addGroupBy("$alias.id, $alias.processed, $alias.createdAt, $alias.lastName")
            ->getQuery()
            ->getResult()
        ;

        if ($filters->isStatusUnprocessed()) {
            return $this->findAssessorRequests($requests);
        }

        foreach ($requests as $k => $request) {
            $requests[$k] = [
                'data' => $request,
                'matchingVotePlacesCount' => 1,
            ];
        }

        return $requests;
    }

    private function findAssessorRequests(array $assessorRequests): array
    {
        if (!\count($assessorRequests)) {
            return [];
        }

        $alias = VotePlaceRepository::ALIAS;

        $qb = $this->_em->createQueryBuilder()
            ->select('COUNT(DISTINCT '.$alias.'.id)')
            ->from(VotePlace::class, $alias)
        ;

        foreach ($assessorRequests as $key => $assessorRequest) {
            $votePlacesCountQuery = clone $qb;

            self::addAndWhereAssessorRequestLocation($votePlacesCountQuery, $assessorRequest, $alias);
            VotePlaceRepository::addAndWhereOfficeAvailability($votePlacesCountQuery, $assessorRequest);

            $assessorRequests[$key] = [
                'data' => $assessorRequest,
                'matchingVotePlacesCount' => (int) $votePlacesCountQuery->getQuery()->getSingleScalarResult(),
            ];
        }

        return $assessorRequests;
    }

    public function countMatchingRequests(Adherent $manager, AssessorRequestFilters $filters): int
    {
        if (!$manager->isAssessorManager()) {
            return 0;
        }

        $qb = $this->createQueryBuilder(self::ALIAS);

        $filters->apply($qb, self::ALIAS);

        self::addAndWhereManagedBy($qb, $manager);

        return (int) $qb
            ->select('COUNT(DISTINCT '.self::ALIAS.'.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private static function addAndWhereManagedBy(QueryBuilder $qb, Adherent $assessorManager): QueryBuilder
    {
        $codesFilter = $qb->expr()->orX();

        foreach ($assessorManager->getAssessorManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->like(self::ALIAS.'.assessorPostalCode', ':code'.$key)
                );
                $qb->setParameter('code'.$key, $code.'%');
            } else {
                // Country
                $codesFilter->add($qb->expr()->eq(self::ALIAS.'.assessorCountry', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        return $qb->andWhere($codesFilter);
    }
}
