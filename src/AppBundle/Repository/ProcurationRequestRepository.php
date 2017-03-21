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

        $proxiesCountSubRequest = $this->_em->createQueryBuilder()
            ->select('COUNT(pp)')
            ->from('AppBundle:ProcurationProxy', 'pp')
            ->where('pp.voteCountry = \'FR\' AND pp.votePostalCode = pr.votePostalCode')
            ->orWhere('pp.voteCountry != \'FR\' AND pp.voteCountry = pr.voteCountry')
            ->getQuery()
            ->getDQL();

        $qb = $this->createQueryBuilder('pr')
            ->select('pr AS data', '('.$proxiesCountSubRequest.') as matchingProxiesCount')
            ->orderBy('pr.createdAt', 'DESC')
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
}
