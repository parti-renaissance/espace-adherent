<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;

abstract class ApplicationRequestRepository extends ServiceEntityRepository
{
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

    public function findForMunicipalChief(Adherent $municipalChief): array
    {
        if (!$municipalChief->isMunicipalChief()) {
            return [];
        }

        $qb = $this->createQueryBuilder('r')
            ->addSelect('CASE WHEN a.id IS NOT NULL THEN 1 ELSE 0 END AS isAdherent')
            ->leftJoin(Adherent::class, 'a', Join::WITH, 'a.emailAddress = r.emailAddress AND a.adherent = 1')
            ->addOrderBy('r.lastName', 'ASC')
            ->addOrderBy('r.firstName', 'ASC')
        ;

        foreach ($municipalChief->getMunicipalChiefManagedArea()->getCodes() as $key => $code) {
            $qb
                ->orWhere("FIND_IN_SET(:codes_$key, r.favoriteCities) > 0")
                ->setParameter("codes_$key", $code)
            ;
        }

        $data = [];
        foreach ($qb->getQuery()->getResult() as $result) {
            /** @var ApplicationRequest $applicationRequest */
            $applicationRequest = $result[0];
            $applicationRequest->setIsAdherent($result['isAdherent']);
            $data[] = $applicationRequest;
        }

        return $data;
    }
}
