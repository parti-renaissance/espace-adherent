<?php

namespace AppBundle\Repository\ApplicationRequest;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractApplicationRequestRepository extends ServiceEntityRepository
{
    private function createListQueryBuilder(string $alias): QueryBuilder
    {
        return $this->createQueryBuilder($alias)
            ->addOrderBy("$alias.lastName", 'ASC')
            ->addOrderBy("$alias.firstName", 'ASC')
        ;
    }

    /**
     * @return VolunteerRequest[]|RunningMateRequest[]
     */
    public function findForReferent(Adherent $referent): array
    {
        if (!$referent->isReferent()) {
            return [];
        }

        return $this->createListQueryBuilder('r')
            ->innerJoin('r.referentTags', 'tag')
            ->andWhere('tag IN (:tags)')
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return VolunteerRequest[]|RunningMateRequest[]
     */
    public function findForMunicipalChief(Adherent $municipalChief): array
    {
        if (!$municipalChief->isMunicipalChief()) {
            return [];
        }

        $qb = $this->createListQueryBuilder('r');

        $orExpression = new Orx();

        foreach ($municipalChief->getMunicipalChiefManagedArea()->getCodes() as $key => $code) {
            $orExpression->add("FIND_IN_SET(:codes_$key, r.favoriteCities) > 0");
            $qb->setParameter("codes_$key", $code);
        }

        return $qb
            ->andWhere($orExpression)
            ->getQuery()
            ->getResult()
        ;
    }

    public function updateAdherentRelation(string $email, ?Adherent $adherent): void
    {
        $this->_em->createQueryBuilder()
            ->update($this->_entityName, 'candidate')
            ->where('candidate.emailAddress = :email')
            ->set('candidate.adherent', ':adherent')
            ->setParameter('email', $email)
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->execute()
        ;
    }
}
