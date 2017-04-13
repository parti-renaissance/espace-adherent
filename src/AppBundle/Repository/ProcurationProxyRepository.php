<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Search\ProcurationParametersFilter;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ProcurationProxyRepository extends EntityRepository
{
    /**
     * @return ProcurationProxy[]
     */
    public function findByEmailAddress(string $emailAddress): array
    {
        return $this
            ->createQueryBuilder('pp')
            ->where('LOWER(pp.emailAddress) = :emailAddress')
            ->setParameter('emailAddress', mb_strtolower($emailAddress))
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Adherent $procurationManager
     * @param int      $page
     * @param int      $perPage
     *
     * @return ProcurationProxy[]
     */
    public function findManagedBy(Adherent $procurationManager, int $page, int $perPage, ProcurationParametersFilter $filters = null): array
    {
        if (!$procurationManager->isProcurationManager()) {
            return [];
        }

        $qb = $this->createQueryBuilder('pp')
            ->addOrderBy('pp.createdAt', 'DESC')
            ->addOrderBy('pp.lastName', 'ASC')
            ->andWhere('pp.reliability >= 0')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
        ;

        $this->addAndWhereManagedBy($qb, $procurationManager);

        if ($filters) {
            $filters->apply($qb, 'pp');
        }

        return $qb->getQuery()->getResult();
    }

    public function countManagedBy(Adherent $procurationManager, ProcurationParametersFilter $filters): int
    {
        if (!$procurationManager->isProcurationManager()) {
            return 0;
        }

        $qb = $this->createQueryBuilder('pp')->select('COUNT(pp)')->andWhere('pp.reliability >= 0');
        $this->addAndWhereManagedBy($qb, $procurationManager);
        $filters->apply($qb, 'pp');

        return (int) $qb->getQuery()->getSingleScalarResult();
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

        $this->addAndWhereManagedBy($qb, $procurationManager);

        return (bool) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param ProcurationRequest $procurationRequest
     *
     * @return array
     */
    public function findMatchingProxies(ProcurationRequest $procurationRequest)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb->select('pp AS data', $this->createMatchingScore($qb, $procurationRequest).' + pp.reliability AS score')
            ->where($qb->expr()->orX(
                $qb->expr()->andX(
                    'pp.voteCountry = \'FR\'',
                    'SUBSTRING(pp.votePostalCode, 1, 2) = SUBSTRING(:votePostalCode, 1, 2)',
                    'pp.voteCityName = :voteCityName'
                ),
                $qb->expr()->andX(
                    'pp.voteCountry != \'FR\'',
                    'pp.voteCountry = :voteCountry'
                )
            ))
            ->andWhere('pp.foundRequest IS NULL')
            ->andWhere('pp.disabled = 0')
            ->andWhere('pp.reliability >= 0')
            ->setParameter('votePostalCode', $procurationRequest->getVotePostalCode())
            ->setParameter('voteCityName', $procurationRequest->getVoteCityName())
            ->setParameter('voteCountry', $procurationRequest->getVoteCountry())
            ->orderBy('score', 'DESC')
            ->addOrderBy('pp.lastName', 'ASC');

        if ($procurationRequest->getElectionPresidentialFirstRound()) {
            $qb->andWhere('pp.electionPresidentialFirstRound = TRUE');
        }

        if ($procurationRequest->getElectionPresidentialSecondRound()) {
            $qb->andWhere('pp.electionPresidentialSecondRound = TRUE');
        }

        if ($procurationRequest->getElectionLegislativeFirstRound()) {
            $qb->andWhere('pp.electionLegislativeFirstRound = TRUE');
        }

        if ($procurationRequest->getElectionLegislativeSecondRound()) {
            $qb->andWhere('pp.electionLegislativeSecondRound = TRUE');
        }

        return $qb->getQuery()->getResult();
    }

    private function addAndWhereManagedBy(QueryBuilder $qb, Adherent $procurationManager)
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
            } else {
                // Country
                $codesFilter->add($qb->expr()->eq('pp.voteCountry', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        $qb->andWhere($codesFilter);
    }

    private function createMatchingScore(QueryBuilder $qb, ProcurationRequest $procurationRequest)
    {
        $elections = [
            'electionPresidentialFirstRound',
            'electionPresidentialSecondRound',
            'electionLegislativeFirstRound',
            'electionLegislativeSecondRound',
        ];

        $score = [];

        foreach ($elections as $election) {
            $score[] = sprintf('(CASE WHEN (pp.%s = :%s) THEN 1 ELSE 0 END)', $election, $election);
        }

        $qb->setParameter('electionPresidentialFirstRound', $procurationRequest->getElectionPresidentialFirstRound());
        $qb->setParameter('electionPresidentialSecondRound', $procurationRequest->getElectionPresidentialSecondRound());
        $qb->setParameter('electionLegislativeFirstRound', $procurationRequest->getElectionLegislativeFirstRound());
        $qb->setParameter('electionLegislativeSecondRound', $procurationRequest->getElectionLegislativeSecondRound());

        return implode(' + ', $score);
    }
}
