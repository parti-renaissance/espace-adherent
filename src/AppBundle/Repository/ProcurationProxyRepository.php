<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use Doctrine\ORM\EntityRepository;

class ProcurationProxyRepository extends EntityRepository
{
    /**
     * @param ProcurationRequest $procurationRequest
     *
     * @return ProcurationProxy[]
     */
    public function findMatchingProxies(ProcurationRequest $procurationRequest)
    {
        return $this->createQueryBuilder('pp')
            ->select('pp')
            ->where('pp.voteCountry = \'FR\' AND pp.votePostalCode = :votePostalCode')
            ->orWhere('pp.voteCountry != \'FR\' AND pp.voteCountry = :voteCountry')
            ->setParameter('votePostalCode', $procurationRequest->getVotePostalCode())
            ->setParameter('voteCountry', $procurationRequest->getVoteCountry())
            ->orderBy('pp.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
