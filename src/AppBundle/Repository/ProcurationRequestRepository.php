<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Procuration\Filter\ProcurationRequestFilters;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ProcurationRequestRepository extends EntityRepository
{
    /**
     * @return ProcurationRequest[]
     */
    public function findByEmailAddress(string $emailAddress): array
    {
        return $this
            ->createQueryBuilder('pr')
            ->where('LOWER(pr.emailAddress) = :emailAddress')
            ->setParameter('emailAddress', mb_strtolower($emailAddress))
            ->getQuery()
            ->getResult()
        ;
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
                'pr.electionPresidentialFirstRound AS request_electionPresidentialFirstRound',
                'pr.electionPresidentialSecondRound AS request_electionPresidentialSecondRound',
                'pr.electionLegislativeFirstRound AS request_electionLegislativeFirstRound',
                'pr.electionLegislativeSecondRound AS request_electionLegislativeSecondRound',
                'pr.reason AS request_reason',
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
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function findMatchingRequests(Adherent $manager, ProcurationRequestFilters $filters): array
    {
        if (!$manager->isProcurationManager()) {
            return [];
        }

        $this->addAndWhereManagedBy($qb = $this->createQueryBuilder('pr'), $manager);
        $filters->apply($qb, 'pr');

        return $this->findRequests($qb);
    }

    public function countMatchingRequests(Adherent $manager, ProcurationRequestFilters $filters): int
    {
        if (!$manager->isProcurationManager()) {
            return 0;
        }

        $this->addAndWhereManagedBy($qb = $this->createQueryBuilder('pr')->select('COUNT(pr.id)'), $manager);
        $filters->apply($qb, 'pr');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function findRequests(QueryBuilder $qb): array
    {
        /** @var ProcurationRequest[] $requests */
        $requests = $qb->getQuery()->getArrayResult();

        $qb = $this->_em->createQueryBuilder();

        $proxiesCountQueryTemplate = $qb
            ->select('COUNT(pp)')
            ->from('AppBundle:ProcurationProxy', 'pp')
            ->andWhere('pp.foundRequest IS NULL')
            ->andWhere('pp.disabled = 0')
            ->andWhere('pp.reliability >= 0')
            ->andWhere($this->createNotMatchingCount().' = 0')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->andX(
                    'pp.voteCountry = \'FR\'',
                    'SUBSTRING(pp.votePostalCode, 1, 2) = :votePostalCodePrefix',
                    'pp.voteCityName = :voteCityName'
                ),
                $qb->expr()->andX(
                    'pp.voteCountry != \'FR\'',
                    'pp.voteCountry = :voteCountry'
                )
            ))
            ->getQuery()
        ;

        foreach ($requests as $key => $request) {
            $proxiesCountQuery = clone $proxiesCountQueryTemplate;
            $proxiesCountQuery->setParameters([
                'votePostalCodePrefix' => substr($request['votePostalCode'], 0, 2),
                'voteCityName' => $request['voteCityName'],
                'voteCountry' => $request['voteCountry'],
                'electionPresidentialFirstRound' => $request['electionPresidentialFirstRound'],
                'electionPresidentialSecondRound' => $request['electionPresidentialSecondRound'],
                'electionLegislativeFirstRound' => $request['electionLegislativeFirstRound'],
                'electionLegislativeSecondRound' => $request['electionLegislativeSecondRound'],
            ]);

            $requests[$key] = [
                'data' => $request,
                'matchingProxiesCount' => (int) $proxiesCountQuery->getSingleScalarResult(),
            ];
        }

        return $requests;
    }

    public function isManagedBy(Adherent $procurationManager, ProcurationRequest $procurationRequest): bool
    {
        if (!$procurationManager->isProcurationManager()) {
            return false;
        }

        $qb = $this->createQueryBuilder('pr')
            ->select('COUNT(pr)')
            ->where('pr.id = :id')
            ->setParameter('id', $procurationRequest->getId());

        $this->addAndWhereManagedBy($qb, $procurationManager);

        return (bool) $qb->getQuery()->getSingleScalarResult();
    }

    public function findRemindersBatchToSend($offset = 0, $limit = 25): array
    {
        return $this->createQueryBuilder('pr')
            ->select('pr', 'pp', 'a')
            ->join('pr.foundProxy', 'pp')
            ->leftJoin('pr.foundBy', 'a')
            ->where('pr.processed = true')
            ->andWhere('pr.reminded = 0')
            ->andWhere('pr.processedAt <= :matchDate')
            ->setParameter('matchDate', new \DateTime('-48 hours'))
            ->orderBy('pr.processedAt', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countRemindersToSend(): int
    {
        return (int) $this->createQueryBuilder('pr')
            ->select('COUNT(pr)')
            ->join('pr.foundProxy', 'pp')
            ->where('pr.processed = true')
            ->andWhere('pr.reminded = 0')
            ->andWhere('pr.processedAt <= :matchDate')
            ->setParameter('matchDate', new \DateTime('-48 hours'))
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function addAndWhereManagedBy(QueryBuilder $qb, Adherent $procurationManager): void
    {
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

        $qb->andWhere($codesFilter);
    }

    private function createNotMatchingCount(): string
    {
        $elections = [
            'electionPresidentialFirstRound',
            'electionPresidentialSecondRound',
            'electionLegislativeFirstRound',
            'electionLegislativeSecondRound',
        ];

        $notMatchingCount = [];

        foreach ($elections as $election) {
            $notMatchingCount[] = sprintf('(CASE WHEN (:%s = TRUE AND pp.%s = FALSE) THEN 1 ELSE 0 END)', $election, $election);
        }

        return implode(' + ', $notMatchingCount);
    }
}
