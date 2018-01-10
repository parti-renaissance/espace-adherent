<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Procuration\Filter\ProcurationProxyProposalFilters;
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
     * @return ProcurationProxy[]
     */
    public function findMatchingProposals(Adherent $manager, ProcurationProxyProposalFilters $filters): array
    {
        if (!$manager->isProcurationManager()) {
            return [];
        }

        $qb = $this->createQueryBuilder('pp');

        $filters->apply($qb, 'pp');

        return $this->addAndWhereManagedBy($qb, $manager)
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
            ->addOrderBy('pp.lastName', 'ASC')
        ;

        return $this->andWhereMatchingRounds($qb, $procurationRequest)
            ->getQuery()
            ->getResult()
        ;
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
            } else {
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
}
