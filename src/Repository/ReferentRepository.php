<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Referent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class ReferentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Referent::class);
    }

    /**
     * @return Referent[]
     */
    public function findByStatus(string $status = Referent::ENABLED): array
    {
        $qb = $this->createQueryBuilder('lc');

        $qb
            ->where('lc.status = :status')
            ->setParameter('status', $status)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findOneByEmailAndSelectPersonOrgaChart(string $email): Referent
    {
        return $this->createQueryBuilderWithEmail($email)
            ->addSelect('referent_person_links')
            ->getQuery()
            ->getSingleResult()
        ;
    }

    public function findOneByEmail(string $email): ?Referent
    {
        return $this->createQueryBuilderWithEmail($email)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    private function createQueryBuilderWithEmail(string $email): QueryBuilder
    {
        return $this->createQueryBuilder('referent')
            ->leftJoin('referent.referentPersonLinks', 'referent_person_links')
            ->where('referent.emailAddress = :email')
            ->setParameter('email', $email)
        ;
    }
}
