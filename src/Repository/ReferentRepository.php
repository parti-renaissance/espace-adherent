<?php

namespace App\Repository;

use App\Entity\Referent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ReferentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Referent::class);
    }

    public function findByStatusOrderedByAreaLabel(string $status = Referent::ENABLED): array
    {
        return $this->createQueryBuilder('referent')
            ->where('referent.status = :status')
            ->setParameter('status', $status)
            ->orderBy('referent.areaLabel')
            ->getQuery()
            ->getResult()
        ;
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
