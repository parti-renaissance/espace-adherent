<?php

namespace AppBundle\Repository\ApplicationRequest;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;

abstract class AbstractApplicationRequestRepository extends ServiceEntityRepository
{
    /**
     * @return VolunteerRequest[]|RunningMateRequest[]
     */
    public function findForReferent(Adherent $referent): array
    {
        if (!$referent->isReferent()) {
            return [];
        }

        $results = $this->createQueryBuilder('r')
            ->addSelect('CASE WHEN a.id IS NOT NULL THEN 1 ELSE 0 END AS isAdherent')
            ->leftJoin(Adherent::class, 'a', Join::WITH, 'a.emailAddress = r.emailAddress AND a.adherent = 1')
            ->innerJoin('r.referentTags', 'tag')
            ->andWhere('tag IN (:tags)')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->addOrderBy('r.lastName', 'ASC')
            ->addOrderBy('r.firstName', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        $data = [];
        foreach ($results as $result) {
            /** @var ApplicationRequest $applicationRequest */
            $applicationRequest = $result[0];
            $applicationRequest->setIsAdherent($result['isAdherent']);
            $data[] = $applicationRequest;
        }

        return $data;
    }
}
