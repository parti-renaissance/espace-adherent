<?php

namespace App\Repository\Election;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Assessor\Filter\CitiesFilters;
use App\Assessor\Filter\VotePlaceFilters;
use App\Entity\Adherent;
use App\Entity\AssessorOfficeEnum;
use App\Entity\AssessorRequest;
use App\Entity\Election\VotePlace;
use App\Repository\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class VotePlaceRepository extends ServiceEntityRepository
{
    use PaginatorTrait;

    private const ALIAS = 'vote_place';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VotePlace::class);
    }

    public function findByCountry(string $country): array
    {
        return $this
            ->createQueryBuilder(self::ALIAS)
            ->andWhere(self::ALIAS.'.postAddress.country = :country')
            ->setParameters([
                'country' => $country,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByPostalCode(string $postalCode, string $city): array
    {
        return $this
            ->createQueryBuilder(self::ALIAS)
            ->andWhere(sprintf('FIND_IN_SET(:postal_code, %s.postAddress.postalCode) > 0', self::ALIAS))
            ->andWhere(self::ALIAS.'.postAddress.cityName = :city')
            ->setParameters([
                'postal_code' => $postalCode,
                'city' => $city,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllByIds(array $ids): array
    {
        return $this->findBy(['id' => $ids]);
    }

    public function findMatchingProposals(Adherent $manager, VotePlaceFilters $filters): PaginatorInterface
    {
        $qb = $this->createQueryBuilder(self::ALIAS);

        $filters->apply($qb, self::ALIAS);

        self::addAndWhereManagedBy($qb, $manager->getAssessorManagedArea()->getCodes());

        return $this->configurePaginator($qb, 1);
    }

    public function getOfficeAvailabilities(array $votePlaceIds): array
    {
        return $this
            ->createQueryBuilder(self::ALIAS, self::ALIAS.'.id')
            ->select(
                self::ALIAS.'.id',
                'COUNT(assessor_request_holder.id) AS holder_count',
                'GROUP_CONCAT(CONCAT_WS(\'|\', assessor_request_holder.uuid, assessor_request_holder.firstName, assessor_request_holder.lastName)) AS holder_uuids',
                'COUNT(assessor_request_substitute.id) AS substitute_count',
                'GROUP_CONCAT(CONCAT_WS(\'|\', assessor_request_substitute.uuid, assessor_request_substitute.firstName, assessor_request_substitute.lastName)) AS substitute_uuids',
            )
            ->leftJoin(
                AssessorRequest::class,
                'assessor_request_holder',
                Join::WITH,
                'assessor_request_holder.office = :holder_office AND assessor_request_holder.votePlace = '.self::ALIAS
            )
            ->leftJoin(
                AssessorRequest::class,
                'assessor_request_substitute',
                Join::WITH,
                'assessor_request_substitute.office = :substitute_office AND assessor_request_substitute.votePlace = '.self::ALIAS
            )
            ->where(self::ALIAS.'.id IN (:ids)')
            ->setParameter('ids', $votePlaceIds)
            ->setParameter('holder_office', AssessorOfficeEnum::HOLDER)
            ->setParameter('substitute_office', AssessorOfficeEnum::SUBSTITUTE)
            ->groupBy(self::ALIAS.'.id')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findMatchingVotePlaces(AssessorRequest $assessorRequest): array
    {
        $qb = $this->createQueryBuilder(self::ALIAS);

        if ($assessorRequest->isFrenchAssessorRequest()) {
            $qb
                ->andWhere(sprintf('FIND_IN_SET(:postalCode, %s.postAddress.postalCode) > 0', self::ALIAS))
                ->setParameter('postalCode', $assessorRequest->getAssessorPostalCode())
                ->andWhere(self::ALIAS.'.postAddress.cityName = :city')
                ->setParameter('city', $assessorRequest->getAssessorCity())
            ;
        } else {
            $qb
                ->andWhere(self::ALIAS.'.postAddress.country = :countryCode')
                ->setParameter('countryCode', $assessorRequest->getAssessorCountry())
            ;
        }

        if (AssessorOfficeEnum::HOLDER === $assessorRequest->getOffice()) {
            $qb
                ->leftJoin(
                    AssessorRequest::class,
                    'assessor_request_holder',
                    Join::WITH,
                    'assessor_request_holder.office = :holder_office AND assessor_request_holder.votePlace = '.self::ALIAS
                )
                ->setParameter('holder_office', AssessorOfficeEnum::HOLDER)
                ->andWhere('assessor_request_holder.id IS NULL')
            ;
        } else {
            $qb
                ->leftJoin(
                    AssessorRequest::class,
                    'assessor_request_substitute',
                    Join::WITH,
                    'assessor_request_substitute.office = :substitute_office AND assessor_request_substitute.votePlace = '.self::ALIAS
                )
                ->setParameter('substitute_office', AssessorOfficeEnum::SUBSTITUTE)
                ->andWhere('assessor_request_substitute.id IS NULL')
            ;
        }

        $qb->addOrderBy(self::ALIAS.'.name', 'ASC');

        if ($assessorRequest->getVotePlaceWishes()->count() > 0) {
            $votePlacesWishedIds = array_map(function ($votePlace) { return $votePlace->getId(); }, $assessorRequest->getVotePlaceWishes()->toArray());

            $votePlacesWished = clone $qb;
            $votePlacesWished->andWhere($votePlacesWished->expr()->in(self::ALIAS.'.id', $votePlacesWishedIds));

            $qb->andWhere($votePlacesWished->expr()->notIn(self::ALIAS.'.id', $votePlacesWishedIds));

            return array_merge(
                $votePlacesWished->getQuery()->getResult(),
                $qb->getQuery()->getResult()
            );
        }

        return $qb->getQuery()->getResult();
    }

    public function findVotePlacesCities(Adherent $manager, CitiesFilters $filters): array
    {
        $qb = $this->createQueryBuilder(self::ALIAS);

        $filters->apply($qb, self::ALIAS);

        self::addAndWhereManagedBy($qb, $manager->getAssessorManagedArea()->getCodes());

        $qb
            ->select(
                self::ALIAS.'.postAddress.cityName AS city',
                self::ALIAS.'.postAddress.postalCode AS postalCode'
            )
            ->addSelect(sprintf('SUBSTRING(%s.code, 1, 5) AS city_code', self::ALIAS))
            ->addSelect('COUNT(DISTINCT '.self::ALIAS.'.id) AS nb_vote_places')
            ->addSelect('COUNT(DISTINCT assessor) AS nb_assessors')
            ->leftJoin(AssessorRequest::class, 'assessor', Join::WITH, 'assessor.votePlace ='.self::ALIAS)
            ->addGroupBy('city_code')
            ->addOrderBy(self::ALIAS.'.postAddress.cityName', 'ASC')
        ;

        if (CitiesFilters::ASSOCIATED === $filters->getStatus()) {
            $qb->having('nb_assessors > 0');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return VotePlace[]
     */
    public function findForCityAssessors(Adherent $manager, string $cityCode): array
    {
        if (!$manager->isAssessorManager()) {
            throw new \InvalidArgumentException('Adherent must be an assessor manager.');
        }

        $qb = $this->createQueryBuilder(self::ALIAS);

        self::addAndWhereManagedBy($qb, $manager->getAssessorManagedArea()->getCodes());

        $rows = $qb
            ->addSelect('assessor')
            ->innerJoin(AssessorRequest::class, 'assessor', Join::WITH, 'assessor.votePlace ='.self::ALIAS.'.id')
            ->andWhere(self::ALIAS.'.code LIKE :city')
            ->setParameter('city', $cityCode.'_%')
            ->getQuery()
            ->getResult()
        ;

        $data = [];
        foreach ($rows as $row) {
            if ($row instanceof VotePlace) {
                if (!isset($data[$row->getId()])) {
                    $data[$row->getId()] = [
                        'vote_place' => $row,
                        'assessors' => [],
                    ];
                }
            } elseif ($row instanceof AssessorRequest) {
                $data[$row->getVotePlace()->getId()]['assessors'][] = $row;
            }
        }

        return $data;
    }

    private static function addAndWhereManagedBy(QueryBuilder $qb, array $codes): void
    {
        if (\in_array('ALL', $codes, true)) {
            return;
        }

        $zoneJoined = false;
        $codesFilter = $qb->expr()->orX();

        foreach ($codes as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->like(self::ALIAS.'.postAddress.postalCode', ':code'.$key)
                );
                $qb->setParameter('code'.$key, $code.'%');
            } elseif (str_starts_with($code, 'CIRCO_')) {
                // District
                if (!$zoneJoined) {
                    $zoneJoined = true;
                    $qb->leftJoin(self::ALIAS.'.zone', 'vote_place_zone');
                }
                $codesFilter->add('vote_place_zone.code = :code'.$key);
                $qb->setParameter('code'.$key, explode('_', $code, 2)[1]);
            } else {
                // Country
                $codesFilter->add($qb->expr()->eq(self::ALIAS.'.postAddress.country', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        $qb->andWhere($codesFilter);
    }
}
