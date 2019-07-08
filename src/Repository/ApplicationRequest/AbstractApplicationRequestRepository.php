<?php

namespace AppBundle\Repository\ApplicationRequest;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use AppBundle\Entity\ReferentTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractApplicationRequestRepository extends ServiceEntityRepository
{
    private function createListQueryBuilder(string $alias): QueryBuilder
    {
        return $this->createQueryBuilder($alias)
            ->addSelect('tag')
            ->leftJoin("$alias.tags", 'tag')
            ->orderBy("$alias.lastName", 'ASC')
            ->addOrderBy("$alias.firstName", 'ASC')
        ;
    }

    /**
     * @var ReferentTag[]
     *
     * @return VolunteerRequest[]|RunningMateRequest[]
     */
    public function findForReferentTags(array $referentTags): array
    {
        return $this->createListQueryBuilder('r')
            ->innerJoin('r.referentTags', 'refTag')
            ->andWhere('refTag IN (:tags)')
            ->setParameter('tags', $referentTags)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return VolunteerRequest|RunningMateRequest|null
     */
    public function findOneByUuid(string $uuid): ?ApplicationRequest
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    /**
     * @return VolunteerRequest[]|RunningMateRequest[]
     */
    public function findAllForInseeCodes(array $inseeCodes): array
    {
        $qb = $this->createListQueryBuilder('r');

        $orExpression = new Orx();

        foreach ($inseeCodes as $key => $code) {
            $orExpression->add("FIND_IN_SET(:codes_$key, r.favoriteCities) > 0");
            $qb->setParameter("codes_$key", $code);
        }

        return $qb
            ->andWhere($orExpression)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return VolunteerRequest[]|RunningMateRequest[]
     */
    public function findAllTakenFor(array $inseeCodes): array
    {
        return $this->createListQueryBuilder('r')
            ->where('r.takenForCity IN (:cities)')
            ->setParameter('cities', $inseeCodes)
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
