<?php

namespace App\Repository;

use App\Entity\ProcurationProxyElectionRound;
use App\Entity\ProcurationRequest;
use Doctrine\ORM\QueryBuilder;

trait ProcurationTrait
{
    public function andWhereMatchingRounds(QueryBuilder $qb, ProcurationRequest $procurationRequest): QueryBuilder
    {
        if (!$procurationRequest->getElectionRounds()->toArray()) {
            return $qb;
        }

        $matches = [];
        foreach ($procurationRequest->getElectionRounds() as $i => $round) {
            $subElectionsQuery = $this->getEntityManager()->createQueryBuilder()
                ->from(ProcurationProxyElectionRound::class, "ppElectionRound_$i")
                ->innerJoin("ppElectionRound_$i.electionRound", "er_$i")
                ->select("er_$i.id")
                ->where("ppElectionRound_$i.procurationProxy = pp")
                ->andWhere(sprintf(
                    'ppElectionRound_%s.%s = :true',
                    $i,
                    $procurationRequest->isRequestFromFrance() ? 'frenchRequestAvailable' : 'foreignRequestAvailable'))
                ->getDQL()
            ;

            $matches[] = $qb->expr()->andX(sprintf(":round_$i IN (%s)", $subElectionsQuery));
            $qb->setParameter("round_$i", $round->getId());
            $qb->setParameter('true', true);
        }

        return $qb->andWhere($qb->expr()->andX(...$matches));
    }
}
