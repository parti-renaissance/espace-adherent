<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\ProcurationRequest;
use App\Procuration\Filter\ProcurationRequestFilters;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ProcurationRequestRepository extends ServiceEntityRepository
{
    use ProcurationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProcurationRequest::class);
    }

    /**
     * @return ProcurationRequest[]
     */
    public function findAllForExport(): array
    {
        return $this->createQueryBuilder('pr')
            ->select(
                'pr.id AS request_id',
                'pr.gender AS request_birthdate',
                'pr.firstNames AS request_firstNames',
                'pr.lastName AS request_lastName',
                'pr.emailAddress AS request_emailAddress',
                'pr.address AS request_address',
                'pr.postalCode AS request_postalCode',
                'pr.city AS request_city',
                'pr.cityName AS request_cityName',
                'pr.country AS request_country',
                'pr.voteOffice AS request_voteOffice',
                'pr.votePostalCode AS request_votePostalCode',
                'pr.voteCity AS request_voteCity',
                'pr.voteCityName AS request_voteCityName',
                'pr.voteCountry AS request_voteCountry',
                'GROUP_CONCAT(er.label SEPARATOR \'\\n\') AS request_electionRounds',
                'pr.processedAt AS request_processedAt',
                'pp.id AS proposal_id',
                'pp.gender AS proposal_birthdate',
                'pp.firstNames AS proposal_firstNames',
                'pp.lastName AS proposal_lastName',
                'pp.emailAddress AS proposal_emailAddress',
                'pp.address AS proposal_address',
                'pp.postalCode AS proposal_postalCode',
                'pp.city AS proposal_city',
                'pp.cityName AS proposal_cityName',
                'pp.country AS proposal_country',
                'pp.voteOffice AS proposal_voteOffice',
                'pp.votePostalCode AS proposal_votePostalCode',
                'pp.voteCity AS proposal_voteCity',
                'pp.voteCityName AS proposal_voteCityName',
                'pp.voteCountry AS proposal_voteCountry',
                'pp.electionPresidentialFirstRound AS proposal_electionPresidentialFirstRound',
                'pp.electionPresidentialSecondRound AS proposal_electionPresidentialSecondRound',
                'pp.electionLegislativeFirstRound AS proposal_electionLegislativeFirstRound',
                'pp.electionLegislativeSecondRound AS proposal_electionLegislativeSecondRound'
            )
            ->join('pr.foundProxy', 'pp')
            ->leftJoin('pr.electionRounds', 'er')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function findMatchingRequests(Adherent $manager, ProcurationRequestFilters $filters): array
    {
        if (!$manager->isProcurationsManager()) {
            return [];
        }

        $qb = $this->createQueryBuilder($alias = 'pr');

        $filters->apply($qb, $alias);

        $requests = $this->addAndWhereManagedBy($qb, $manager)
            ->addGroupBy("$alias.id")
            ->getQuery()
            ->getResult()
        ;

        if ($filters->matchUnprocessedRequests()) {
            return $this->findRequests($requests);
        }

        foreach ($requests as $k => $request) {
            $requests[$k] = [
                'data' => $request,
                'matchingProxiesCount' => 1,
            ];
        }

        return $requests;
    }

    public function countMatchingRequests(Adherent $manager, ProcurationRequestFilters $filters): int
    {
        if (!$manager->isProcurationsManager()) {
            return 0;
        }

        $qb = $this
            ->createQueryBuilder('pr')
            ->where('pr.enabled = :enabled')
            ->setParameter('enabled', true)
        ;

        $filters->apply($qb, 'pr');

        return (int) $this->addAndWhereManagedBy($qb, $manager)
            ->select('COUNT(DISTINCT pr.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param ProcurationRequest[] $requests
     */
    private function findRequests(array $requests): array
    {
        if (!\count($requests)) {
            return [];
        }

        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('COUNT(DISTINCT pp.id)')
            ->from('App:ProcurationProxy', 'pp')
            ->leftJoin('pp.otherVoteCities', 'other_city')
            ->andWhere('pp.disabled = 0')
            ->andWhere('pp.reliability >= 0')
        ;

        /**
         * @var ProcurationRequest
         */
        foreach ($requests as $key => $request) {
            $proxiesCountQuery = $this->andWhereMatchingRounds(clone $qb, $request);
            $proxiesCountQuery = ProcurationProxyRepository::addAndWhereCountryConditions($proxiesCountQuery, $request, true);

            $proxiesCountQuery->setParameter('votePostalCodePrefix', substr($request->getVotePostalCode(), 0, 2));
            $proxiesCountQuery->setParameter('voteCityName', $request->getVoteCityName());
            if ($request->isRequestFromFrance()) {
                $proxiesCountQuery->setParameter('voteCityInsee', $request->getVoteCityInsee());
            }
            $proxiesCountQuery->setParameter('voteCountry', $request->getVoteCountry());

            $requests[$key] = [
                'data' => $request,
                'matchingProxiesCount' => (int) $proxiesCountQuery->getQuery()->getSingleScalarResult(),
            ];
        }

        return $requests;
    }

    public function isManagedBy(Adherent $procurationManager, ProcurationRequest $procurationRequest): bool
    {
        if (!$procurationManager->isProcurationsManager()) {
            return false;
        }

        $qb = $this->createQueryBuilder('pr')
            ->select('COUNT(pr)')
            ->where('pr.id = :id')
            ->setParameter('id', $procurationRequest->getId())
        ;

        return (bool) $this->addAndWhereManagedBy($qb, $procurationManager)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function createQueryBuilderForReminders(\DateTime $processedAfter, int $limit): QueryBuilder
    {
        return $this->createQueryBuilder('pr')
            ->select('pr', 'pp')
            ->innerJoin('pr.foundProxy', 'pp')
            ->andWhere('pr.processed = true')
            ->andWhere('pr.processedAt > :processed_after')
            ->andWhere('pr.enabled = :enabled')
            ->andWhere('pp.disabled != :enabled')
            ->setParameter('processed_after', $processedAfter)
            ->setParameter('enabled', true)
            ->orderBy('pr.processedAt', 'ASC')
            ->setMaxResults($limit)
        ;
    }

    private function addAndWhereManagedBy(QueryBuilder $qb, Adherent $procurationManager): QueryBuilder
    {
        if ($procurationManager->getProcurationManagedArea()->getCodes() === ['ALL']) {
            return $qb;
        }

        $codesFilter = $qb->expr()->orX();

        foreach ($procurationManager->getProcurationManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->andX(
                        'pr.voteCountry = \'FR\'',
                        $qb->expr()->like('pr.votePostalCode', ':code'.$key)
                    )
                );
                $qb->setParameter('code'.$key, $code.'%');
            } else {
                // Country
                $codesFilter->add($qb->expr()->eq('pr.voteCountry', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        return $qb->andWhere($codesFilter);
    }
}
