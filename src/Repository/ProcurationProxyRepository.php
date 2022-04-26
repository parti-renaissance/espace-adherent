<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use App\Intl\FranceCitiesBundle;
use App\Procuration\Filter\ProcurationProxyProposalFilters;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ProcurationProxyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProcurationProxy::class);
    }

    /**
     * @return ProcurationProxy[]
     */
    public function findMatchingProposals(Adherent $manager, ProcurationProxyProposalFilters $filters): array
    {
        if (!$manager->isProcurationManager()) {
            return [];
        }

        $qb = $this->createQueryBuilder($alias = 'pp');

        $filters->apply($qb, $alias);

        return $this->addAndWhereManagedBy($qb, $manager)
            ->addGroupBy("$alias.id")
            ->getQuery()
            ->getResult()
        ;
    }

    public function countMatchingProposals(Adherent $manager, ProcurationProxyProposalFilters $filters)
    {
        if (!$manager->isProcurationManager()) {
            return 0;
        }

        $qb = $this->createQueryBuilder('pp');

        $filters->apply($qb, 'pp');

        return $this->addAndWhereManagedBy($qb, $manager)
            ->select('COUNT(DISTINCT pp.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function isManagedBy(Adherent $procurationManager, ProcurationProxy $proxy): bool
    {
        if (!$procurationManager->isProcurationManager()) {
            return false;
        }

        $qb = $this->createQueryBuilder('pp')
            ->select('COUNT(pp)')
            ->where('pp.id = :id')
            ->andWhere('pp.reliability >= 0')
            ->setParameter('id', $proxy->getId())
        ;

        return (bool) $this->addAndWhereManagedBy($qb, $procurationManager)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findMatchingProxies(ProcurationRequest $procurationRequest): array
    {
        $qb = $this->createQueryBuilder('pp');

        $qb
            ->select('pp AS data', $this->createMatchingScore($qb, $procurationRequest).' + pp.reliability AS score')
            ->andWhere('pp.disabled = 0')
            ->andWhere('pp.reliability >= 0')
            ->setParameter('votePostalCodePrefix', substr($procurationRequest->getVotePostalCode(), 0, 2))
            ->setParameter('voteCityName', $procurationRequest->getVoteCityName())
            ->setParameter('voteCountry', $procurationRequest->getVoteCountry())
            ->orderBy('score', 'DESC')
            ->addOrderBy('pp.lastName', 'ASC')
        ;

        $this->addAndWhereCountryConditions($qb, $procurationRequest);

        $this->andWhereMatchingRounds($qb, $procurationRequest);

        return $qb->getQuery()->getResult();
    }

    public function findMatchingProxiesByOtherCities(ProcurationRequest $procurationRequest): array
    {
        if (!$procurationRequest->isRequestFromFrance()) {
            return [];
        }

        $qb = $this->createQueryBuilder('pp');

        $voteCityInsee = $procurationRequest->getVoteCityInsee();
        $conditions = ['other_city.code = :voteCityInsee'];
        $specialCityCode = FranceCitiesBundle::SPECIAL_CITY_INSEE_CODE[$voteCityInsee] ?? null;

        if ($specialCityCode) {
            $conditions[] = sprintf('other_city.code = :special_city_code');
            $qb->setParameter('special_city_code', $specialCityCode);
        }

        $qb
            ->select('pp AS data', $this->createMatchingScore($qb, $procurationRequest).' + pp.reliability AS score')
            ->innerJoin('pp.otherVoteCities', 'other_city')
            ->andWhere('pp.disabled = 0')
            ->andWhere('pp.reliability >= 0')
            ->andWhere('pp.frenchRequestAvailable = true')
            ->andWhere(
                $qb->expr()->andX(
                    'pp.voteCountry = :voteCountry',
                    'pp.voteCityName != :voteCityName',
                    '('.implode(' OR ', $conditions).')'
                )
            )
            ->setParameter('voteCityName', $procurationRequest->getVoteCityName())
            ->setParameter('voteCityInsee', $procurationRequest->getVoteCityInsee())
            ->setParameter('voteCountry', $procurationRequest->getVoteCountry())
            ->orderBy('score', 'DESC')
            ->addOrderBy('pp.lastName', 'ASC')
        ;

        $this->andWhereMatchingRounds($qb, $procurationRequest);

        return $qb->getQuery()->getResult();
    }

    private function addAndWhereManagedBy(QueryBuilder $qb, Adherent $procurationManager): QueryBuilder
    {
        $codesFilter = $qb->expr()->orX();

        foreach ($procurationManager->getProcurationManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->andX(
                        'pp.voteCountry = \'FR\'',
                        $qb->expr()->like('pp.votePostalCode', ':code'.$key)
                    )
                );

                $qb->setParameter('code'.$key, $code.'%');
            } elseif ('all' !== strtolower($code)) {
                // Country
                $codesFilter->add($qb->expr()->eq('pp.voteCountry', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        return $qb->andWhere($codesFilter);
    }

    private function andWhereMatchingRounds(QueryBuilder $qb, ProcurationRequest $procurationRequest): QueryBuilder
    {
        $matches = [];
        foreach ($procurationRequest->getElectionRounds() as $i => $round) {
            $matches[] = $qb->expr()->andX(":round_$i MEMBER OF pp.electionRounds");
            $qb->setParameter("round_$i", $round->getId());
        }

        return $qb->andWhere($qb->expr()->andX(...$matches));
    }

    private function createMatchingScore(QueryBuilder $qb, ProcurationRequest $procurationRequest): string
    {
        foreach ($procurationRequest->getElectionRounds() as $i => $round) {
            $score[] = "(CASE WHEN (:round_$i MEMBER OF pp.electionRounds) THEN 1 ELSE 0 END)";

            $qb->setParameter("round_$i", $round->getId());
        }

        return implode(' + ', $score ?? []);
    }

    public static function addAndWhereCountryConditions(
        QueryBuilder $qb,
        ProcurationRequest $request,
        bool $withOtherCities = false
    ): QueryBuilder {
        if ($request->isRequestFromFrance()) {
            $cityCondition = $qb->expr()->andX(
                'SUBSTRING(pp.votePostalCode, 1, 2) = :votePostalCodePrefix',
                'pp.voteCityName = :voteCityName'
            );

            if ($withOtherCities) {
                $voteCityInsee = $request->getVoteCityInsee();
                $conditions = ['other_city.code = :voteCityInsee'];
                $specialCityCode = FranceCitiesBundle::SPECIAL_CITY_INSEE_CODE[$voteCityInsee] ?? null;

                if ($specialCityCode) {
                    $conditions[] = sprintf('other_city.code = :special_city_code');
                    $qb->setParameter('special_city_code', $specialCityCode);
                }

                $cityCondition = $qb->expr()->orX(
                    $cityCondition,
                    '(pp.voteCityName != :voteCityName AND ('.implode(' OR ', $conditions).'))'
                );
            }

            return $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        'pp.voteCountry = \'FR\'',
                        $cityCondition,
                        'pp.frenchRequestAvailable = true'
                    ),
                    $qb->expr()->andX(
                        'pp.voteCountry != \'FR\'',
                        'pp.voteCountry = :voteCountry',
                        'pp.frenchRequestAvailable = true'
                    )
                )
            );
        }

        return $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->andX(
                    'pp.voteCountry = \'FR\'',
                    'SUBSTRING(pp.votePostalCode, 1, 2) = :votePostalCodePrefix',
                    'pp.voteCityName = :voteCityName',
                    'pp.foreignRequestAvailable = true'
                ),
                $qb->expr()->andX(
                    'pp.voteCountry != \'FR\'',
                    'pp.voteCountry = :voteCountry',
                    'pp.foreignRequestAvailable = true'
                )
            )
        );
    }

    public function createQueryBuilderForReminders(\DateTime $processedAfter, int $limit): QueryBuilder
    {
        return $this->createQueryBuilder('pp')
            ->select('pp')
            ->innerJoin(ProcurationRequest::class, 'pr', Join::WITH, 'pr.foundProxy = pp')
            ->andWhere('pr.processed = true')
            ->andWhere('pr.processedAt > :processed_after')
            ->andWhere('pr.enabled = :enabled')
            ->andWhere('pp.disabled != :enabled')
            ->setParameter('processed_after', $processedAfter)
            ->setParameter('enabled', true)
            ->setMaxResults($limit)
        ;
    }
}
