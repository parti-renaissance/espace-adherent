<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationRequest;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ProcurationRequestRepository extends EntityRepository
{
    /**
     * @param Adherent $procurationManager
     *
     * @return array
     */
    public function findManagedBy(Adherent $procurationManager)
    {
        if (!$procurationManager->isProcurationManager()) {
            return [];
        }

        $qb = $this->_em->createQueryBuilder();

        $proxiesCountSubRequest = $qb
            ->select('COUNT(pp)')
            ->from('AppBundle:ProcurationProxy', 'pp')
            ->where($qb->expr()->orX(
                'pp.voteCountry = \'FR\' AND pp.voteCity = pr.voteCity',
                'pp.voteCountry != \'FR\' AND pp.voteCountry = pr.voteCountry'
            ))
            ->andWhere('pp.foundRequest IS NULL')
            ->andWhere($this->createNotMatchingCount().' = 0')
            ->getQuery()
            ->getDQL();

        $qb = $this->createQueryBuilder('pr')
            ->select('pr AS data', '('.$proxiesCountSubRequest.') as matchingProxiesCount')
            ->orderBy('pr.processed', 'ASC')
            ->addOrderBy('pr.createdAt', 'DESC')
            ->addOrderBy('pr.lastName', 'ASC');

        $this->addAndWhereManagedBy($qb, $procurationManager);

        return $qb->getQuery()->getArrayResult();
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

    private function addAndWhereManagedBy(QueryBuilder $qb, Adherent $procurationManager)
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

    private function createNotMatchingCount()
    {
        $elections = [
            'electionPresidentialFirstRound',
            'electionPresidentialSecondRound',
            'electionLegislativeFirstRound',
            'electionLegislativeSecondRound',
        ];

        $notMatchingCount = [];

        foreach ($elections as $election) {
            $notMatchingCount[] = sprintf('(CASE WHEN (pr.%s = TRUE AND pp.%s = FALSE) THEN 1 ELSE 0 END)', $election, $election);
        }

        return implode(' + ', $notMatchingCount);
    }
}
