<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ProcurationProxyRepository extends EntityRepository
{
    /**
     * @param ProcurationRequest $procurationRequest
     *
     * @return array
     */
    public function findMatchingProxies(ProcurationRequest $procurationRequest)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb->select('pp AS data', $this->createMatchingScore($qb, $procurationRequest).' AS score')
            ->where('pp.voteCountry = \'FR\' AND pp.votePostalCode = :votePostalCode')
            ->orWhere('pp.voteCountry != \'FR\' AND pp.voteCountry = :voteCountry')
            ->setParameter('votePostalCode', $procurationRequest->getVotePostalCode())
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

    /**
     * @param ProcurationRequest $procurationRequest
     *
     * @return array
     */
    public function isMatching(ProcurationRequest $procurationRequest, ProcurationProxy $procurationProxy)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb->select('pp AS data', $this->createMatchingScore($qb, $procurationRequest).' AS score')
            ->where('pp.voteCountry = \'FR\' AND pp.votePostalCode = :votePostalCode')
            ->orWhere('pp.voteCountry != \'FR\' AND pp.voteCountry = :voteCountry')
            ->setParameter('votePostalCode', $procurationRequest->getVotePostalCode())
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
