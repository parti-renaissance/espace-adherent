<?php

namespace AppBundle\Repository;

use AppBundle\Assessor\Filter\VotePlaceFilters;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AssessorOfficeEnum;
use AppBundle\Entity\AssessorRequest;
use AppBundle\Entity\VotePlace;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class VotePlaceRepository extends AbstractAssessorRepository
{
    public const ALIAS = 'vp';

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VotePlace::class);
    }

    public function findMatchingProposals(Adherent $manager, VotePlaceFilters $filters): array
    {
        if (!$manager->isAssessorManager()) {
            return [];
        }

        $qb = $this->createQueryBuilder(self::ALIAS);

        $filters->apply($qb, self::ALIAS);

        self::addAndWhereManagedBy($qb, $manager);

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public function countMatchingProposals(Adherent $manager, VotePlaceFilters $filters): int
    {
        if (!$manager->isAssessorManager()) {
            return 0;
        }

        $qb = $this->createQueryBuilder(self::ALIAS);

        $filters->apply($qb, self::ALIAS);

        self::addAndWhereManagedBy($qb, $manager);

        return (int) $qb
            ->select('COUNT(DISTINCT vp.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findMatchingVotePlaces(AssessorRequest $assessorRequest): array
    {
        $qb = $this->createQueryBuilder(self::ALIAS);

        self::addAndWherePostalCodeFindInSet($qb, $assessorRequest, self::ALIAS);
        self::addAndWhereOfficeAvailability($qb, $assessorRequest);
        self::addAndWhereCity($qb, $assessorRequest);

        $qb->addOrderBy('vp.name', 'ASC');

        if ($assessorRequest->getVotePlacesWishes()->count() > 0) {
            $votePlacesWishedIds = array_map(function ($votePlace) { return $votePlace->getId(); }, $assessorRequest->getVotePlacesWishes()->toArray());

            $votePlacesWished = clone $qb;
            $votePlacesWished->andWhere($votePlacesWished->expr()->in('vp.id', $votePlacesWishedIds));

            $qb->andWhere($votePlacesWished->expr()->notIn('vp.id', $votePlacesWishedIds));

            return array_merge(
                $votePlacesWished->getQuery()->getResult(),
                $qb->getQuery()->getResult()
            );
        }

        return $qb->getQuery()->getResult();
    }

    public static function addAndWhereOfficeAvailability(
        QueryBuilder $qb,
        AssessorRequest $assessorRequest,
        $alias = self::ALIAS
    ): QueryBuilder {
        if (AssessorOfficeEnum::HOLDER === $assessorRequest->getOffice()) {
            $qb->andWhere($alias.'.holderOfficeAvailable = true');
        } else {
            $qb->andWhere($alias.'.substitudeOfficeAvailable = true');
        }

        return $qb;
    }

    public static function addAndWhereCity(QueryBuilder $qb, AssessorRequest $assessorRequest): QueryBuilder
    {
        return $qb
            ->andWhere('vp.city = :city')
            ->setParameter('city', $assessorRequest->getCity())
        ;
    }

    private static function addAndWhereManagedBy(QueryBuilder $qb, Adherent $assessorManager): QueryBuilder
    {
        $codesFilter = $qb->expr()->orX();

        foreach ($assessorManager->getAssessorManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->like(self::ALIAS.'.postalCode', ':code'.$key)
                );
                $qb->setParameter('code'.$key, $code.'%');
            } else {
                // Country
                $codesFilter->add($qb->expr()->eq(self::ALIAS.'.country', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        return $qb->andWhere($codesFilter);
    }
}
